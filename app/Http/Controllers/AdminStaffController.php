<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    public function index()
    {
        return view('admin.staff.index', [
            'staffUsers' => User::query()->where('is_admin', false)->orderBy('id')->get(),
        ]);
    }

    public function show(Request $request, User $user)
    {
        abort_if($user->is_admin, 404);

        $month = CarbonImmutable::parse($request->string('month')->toString() ?: now()->format('Y-m'));
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

    public function exportCsv(Request $request, User $user): StreamedResponse
    {
        abort_if($user->is_admin, 404);

        $month = CarbonImmutable::parse($request->string('month')->toString() ?: now()->format('Y-m'));
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
}
