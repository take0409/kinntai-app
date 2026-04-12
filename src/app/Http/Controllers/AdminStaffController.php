<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    /**
     * 管理者用のスタッフ一覧を表示する。
     */
    public function index()
    {
        return view('admin.staff.index', [
            'staffUsers' => User::query()->where('is_admin', false)->orderBy('id')->get(),
        ]);
    }

    /**
     * 指定スタッフの月別勤怠一覧を表示する。
     */
    public function show(Request $request, User $user)
    {
        abort_if($user->is_admin, 403);

        $month = $this->selectedMonth($request);
        $start = $month->startOfMonth();
        $end = $month->endOfMonth();
        $attendances = Attendance::query()
            ->with('breaks')
            ->where('user_id', $user->id)
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

        return view('admin.staff.monthly', [
            'user' => $user,
            'month' => $month,
            'rows' => $rows,
        ]);
    }

    /**
     * 指定スタッフの月別勤怠をCSVで出力する。
     */
    public function exportCsv(Request $request, User $user): StreamedResponse
    {
        abort_if($user->is_admin, 403);

        $month = $this->selectedMonth($request);
        $start = $month->startOfMonth();
        $end = $month->endOfMonth();
        $attendances = Attendance::query()
            ->with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->work_date->toDateString());

        return response()->streamDownload(function () use ($month, $attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach (range(1, $month->daysInMonth) as $day) {
                $date = $month->day($day);
                $attendance = $attendances->get($date->toDateString());

                fputcsv($handle, [
                    $date->format('Y/m/d'),
                    $attendance?->clock_in_at?->format('H:i') ?? '',
                    $attendance?->clock_out_at?->format('H:i') ?? '',
                    $attendance?->breakDurationLabel() ?? '',
                    $attendance?->workDurationLabel() ?? '',
                ]);
            }

            fclose($handle);
        }, sprintf('staff_%s_%s.csv', $user->id, $month->format('Ym')));
    }

    /**
     * 月指定が不正な場合は当月を返す。
     */
    protected function selectedMonth(Request $request): CarbonImmutable
    {
        $month = $request->string('month')->toString();

        try {
            return CarbonImmutable::parse($month !== '' ? $month : now()->format('Y-m'));
        } catch (\Throwable) {
            return CarbonImmutable::parse(now()->format('Y-m'));
        }
    }
}
