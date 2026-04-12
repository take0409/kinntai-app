<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->timezone(config('app.timezone'));
        $attendance = Attendance::query()
            ->with('breaks')
            ->where('user_id', $request->user()->id)
            ->whereDate('work_date', $today->toDateString())
            ->first();

        return view('attendance.index', [
            'attendance' => $attendance,
            'status' => $attendance?->statusLabel() ?? '勤務外',
            'today' => $today,
        ]);
    }

    public function clockIn(Request $request): RedirectResponse
    {
        $now = now()->timezone(config('app.timezone'));
        $attendance = Attendance::firstOrNew([
            'user_id' => $request->user()->id,
            'work_date' => $now->toDateString(),
        ]);

        if (! $attendance->clock_in_at) {
            $attendance->clock_in_at = $now;
            $attendance->save();
        }

        return back()->with('status', '出勤しました。');
    }

    public function startBreak(Request $request): RedirectResponse
    {
        $attendance = $this->todayAttendance($request);

        if (! $attendance || $attendance->clock_out_at || $attendance->isOnBreak()) {
            return back();
        }

        $attendance->breaks()->create([
            'started_at' => now()->timezone(config('app.timezone')),
        ]);

        return back()->with('status', '休憩に入りました。');
    }

    public function endBreak(Request $request): RedirectResponse
    {
        $attendance = $this->todayAttendance($request);

        if (! $attendance) {
            return back();
        }

        $break = $attendance->breaks()->whereNull('ended_at')->latest('started_at')->first();

        if ($break) {
            $break->update([
                'ended_at' => now()->timezone(config('app.timezone')),
            ]);
        }

        return back()->with('status', '休憩から戻りました。');
    }

    public function clockOut(Request $request): RedirectResponse
    {
        $attendance = $this->todayAttendance($request);

        if (! $attendance || $attendance->isOnBreak()) {
            return back();
        }

        $attendance->update([
            'clock_out_at' => now()->timezone(config('app.timezone')),
        ]);

        return back()->with('status', '退勤しました。');
    }

    protected function todayAttendance(Request $request): ?Attendance
    {
        return Attendance::query()
            ->with('breaks')
            ->where('user_id', $request->user()->id)
            ->whereDate('work_date', now()->timezone(config('app.timezone'))->toDateString())
            ->first();
    }
}
