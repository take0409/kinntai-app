<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * 管理者の日別勤怠一覧を表示する。
     */
    public function index(Request $request)
    {
        $date = $this->targetDate($request);
        $users = User::query()->where('is_admin', false)->orderBy('id')->get();
        $attendances = Attendance::query()
            ->with('breaks')
            ->whereDate('work_date', $date->toDateString())
            ->get()
            ->keyBy('user_id');

        $rows = $users->map(fn (User $user) => [
            'user' => $user,
            'attendance' => $attendances->get($user->id),
        ]);

        return view('admin.attendance.index', [
            'date' => $date,
            'rows' => $rows,
        ]);
    }

    /**
     * 管理者用の勤怠詳細画面を表示する。
     */
    public function show(Attendance $attendance)
    {
        $attendance->load('user', 'breaks');

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'breaks' => $attendance->breaks->values()->pad(2, null)->take(2),
        ]);
    }

    /**
     * 管理者が勤怠詳細を直接修正する。
     */
    public function update(AdminAttendanceUpdateRequest $request, Attendance $attendance): RedirectResponse
    {
        $attendance->update([
            'clock_in_at' => $this->combineDateTime($attendance->work_date->format('Y-m-d'), $request->input('clock_in')),
            'clock_out_at' => $this->combineDateTime($attendance->work_date->format('Y-m-d'), $request->input('clock_out')),
            'note' => $request->string('note')->toString(),
        ]);

        $attendance->breaks()->delete();

        foreach ($request->breakTimes() as $break) {
            $attendance->breaks()->create([
                'started_at' => $this->combineDateTime($attendance->work_date->format('Y-m-d'), $break['start']),
                'ended_at' => $this->combineDateTime($attendance->work_date->format('Y-m-d'), $break['end']),
            ]);
        }

        return back()->with('status', '勤怠情報を更新しました。');
    }

    /**
     * 勤務日と入力時刻を結合して日時に変換する。
     */
    protected function combineDateTime(string $date, string $time): Carbon
    {
        return Carbon::parse("{$date} {$time}", config('app.timezone'));
    }

    /**
     * 日付指定が不正な場合は当日を返す。
     */
    protected function targetDate(Request $request): CarbonImmutable
    {
        $date = $request->string('date')->toString();

        try {
            return CarbonImmutable::parse($date !== '' ? $date : now()->toDateString());
        } catch (\Throwable) {
            return CarbonImmutable::parse(now()->toDateString());
        }
    }
}
