<?php

namespace App\Http\Controllers;

use App\Http\Requests\StampCorrectionApprovalRequest;
use App\Http\Requests\StampCorrectionRequestIndexRequest;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StampCorrectionRequestController extends Controller
{
    public function index(StampCorrectionRequestIndexRequest $request)
    {
        $status = $request->statusFilter();
        $query = StampCorrectionRequest::query()
            ->with(['user', 'attendance'])
            ->where('status', $status === 'approved' ? 'approved' : 'pending')
            ->latest('requested_at');

        if (! $request->user()->is_admin) {
            $query->where('user_id', $request->user()->id);
        }

        return view($request->user()->is_admin ? 'admin.requests.index' : 'requests.index', [
            'requests' => $query->get(),
            'status' => $status,
        ]);
    }

    public function show(StampCorrectionRequest $stampCorrectionRequest)
    {
        $stampCorrectionRequest->load('user', 'attendance');

        return view('admin.requests.show', [
            'requestItem' => $stampCorrectionRequest,
            'breaks' => collect($stampCorrectionRequest->requested_break_times ?? [])->pad(2, null)->take(2),
        ]);
    }

    public function approve(StampCorrectionApprovalRequest $request, StampCorrectionRequest $stampCorrectionRequest): RedirectResponse
    {
        if ($stampCorrectionRequest->status === 'approved') {
            return back();
        }

        $attendance = $stampCorrectionRequest->attendance;

        $attendance->update([
            'clock_in_at' => $stampCorrectionRequest->requested_clock_in_at,
            'clock_out_at' => $stampCorrectionRequest->requested_clock_out_at,
            'note' => $stampCorrectionRequest->note,
        ]);

        $attendance->breaks()->delete();

        foreach ($stampCorrectionRequest->requested_break_times ?? [] as $break) {
            $attendance->breaks()->create([
                'started_at' => Carbon::parse($attendance->work_date->format('Y-m-d').' '.$break['start'], config('app.timezone')),
                'ended_at' => Carbon::parse($attendance->work_date->format('Y-m-d').' '.$break['end'], config('app.timezone')),
            ]);
        }

        $stampCorrectionRequest->update([
            'status' => 'approved',
            'approved_at' => now()->timezone(config('app.timezone')),
            'approved_by' => $request->user()->id,
        ]);

        return back()->with('status', '申請を承認しました。');
    }
}
