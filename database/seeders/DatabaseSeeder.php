<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => '管理者',
            'email' => 'admin@coachtech.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $staffMembers = collect([
            ['name' => '西 侑奈', 'email' => 'reina.n@coachtech.com'],
            ['name' => '山田 太郎', 'email' => 'taro.y@coachtech.com'],
            ['name' => '堀口 一世', 'email' => 'issei.m@coachtech.com'],
            ['name' => '山本 敬吾', 'email' => 'keiichi.y@coachtech.com'],
            ['name' => '秋田 朋美', 'email' => 'tomomi.a@coachtech.com'],
            ['name' => '中西 勝大', 'email' => 'norio.n@coachtech.com'],
        ])->map(function (array $user) {
            return User::query()->create([
                ...$user,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]);
        });

        $targetMonth = now()->startOfMonth();

        $staffMembers->each(function (User $user, int $index) use ($targetMonth, $admin) {
            foreach (range(1, $targetMonth->daysInMonth) as $day) {
                $date = $targetMonth->copy()->day($day);

                $attendance = Attendance::query()->create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in_at' => $date->copy()->setTime(9, 0),
                    'clock_out_at' => $date->copy()->setTime(18, 0),
                    'note' => '',
                ]);

                $attendance->breaks()->create([
                    'started_at' => $date->copy()->setTime(12, 0),
                    'ended_at' => $date->copy()->setTime(13, 0),
                ]);
            }

            if ($index === 0) {
                $attendance = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('work_date', $targetMonth->copy()->day(1)->toDateString())
                    ->first();

                StampCorrectionRequest::query()->create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'requested_clock_in_at' => $attendance->work_date->copy()->setTime(9, 0),
                    'requested_clock_out_at' => $attendance->work_date->copy()->setTime(18, 0),
                    'requested_break_times' => [
                        ['start' => '12:00', 'end' => '13:00'],
                    ],
                    'note' => '電車遅延のため',
                    'status' => 'pending',
                    'requested_at' => $attendance->work_date->copy()->addDay(),
                ]);
            }

            if ($index === 1) {
                $attendance = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('work_date', $targetMonth->copy()->day(2)->toDateString())
                    ->first();

                StampCorrectionRequest::query()->create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'requested_clock_in_at' => $attendance->work_date->copy()->setTime(10, 0),
                    'requested_clock_out_at' => $attendance->work_date->copy()->setTime(19, 0),
                    'requested_break_times' => [
                        ['start' => '13:00', 'end' => '14:00'],
                    ],
                    'note' => '通院のため',
                    'status' => 'approved',
                    'requested_at' => $attendance->work_date->copy()->addDays(2),
                    'approved_at' => $attendance->work_date->copy()->addDays(3),
                    'approved_by' => $admin->id,
                ]);
            }
        });
    }
}
