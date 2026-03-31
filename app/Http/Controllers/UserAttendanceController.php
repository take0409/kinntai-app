<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $month = CarbonImmutable::parse($request->string('month')->toString() ?: now()->format('Y-m'));
        $start = $month->startOfMonth();
        $end = $month->endOfMonth();

        $attendances = Attendance::query()
            ->with('breaks')
            ->where('user_id', $request->user()->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->work_date->toDateString());

        $rows = collect(range(1, $month->daysInMonth))->map(function (int $day) use ($month, $attendances) {
            $date = $month->day($day);

            return [
                'date' => $date,
                'attendance' => $attendances->get($date->toDateString()),
            ];
        });

        return view('attendance.list', [
            'month' => $month,
            'rows' => $rows,
        ]);
    }

    public function show(Request $request, Attendance $attendance)
    {
        abort_unless($attendance->user_id === $request->user()->id, 404);

        $attendance->load(['user', 'breaks', 'pendingCorrectionRequest']);

        return view('attendance.show', [
            'attendance' => $attendance,
            'pendingRequest' => $attendance->pendingCorrectionRequest->first(),
            'breaks' => $attendance->breaks->values()->pad(2, null)->take(2),
        ]);
    }

    public function update(AttendanceCorrectionRequest $request, Attendance $attendance): RedirectResponse
    {
        abort_unless($attendance->user_id === $request->user()->id, 404);

        if ($attendance->pendingCorrectionRequest()->exists()) {
            return back()->withErrors([
                'attendance' => '承認待ちのため修正はできません。',
            ]);
        }

        StampCorrectionRequest::query()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $request->user()->id,
            'requested_clock_in_at' => $this->combineDateTime($attendance->work_date->format('Y-m-d'), $request->input('clock_in')),
            'requested_clock_out_at' => $this->combineDateTime($attendance->work_date->format('Y-m-d'), $request->input('clock_out')),
            'requested_break_times' => $request->breakTimes(),
            'note' => $request->string('note')->toString(),
            'status' => 'pending',
            'requested_at' => now()->timezone(config('app.timezone')),
        ]);

        return redirect('/stamp_correction_request/list')->with('status', '修正申請を送信しました。');
    }

    protected function combineDateTime(string $date, string $time): Carbon
    {
        return Carbon::parse("{$date} {$time}", config('app.timezone'));
    }
}
