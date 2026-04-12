<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_daily_attendance_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = User::factory()->create(['name' => '山田 太郎']);
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'work_date' => '2026-03-01',
            'clock_in_at' => Carbon::parse('2026-03-01 09:00'),
            'clock_out_at' => Carbon::parse('2026-03-01 18:00'),
            'note' => '',
        ]);
        $attendance->breaks()->create([
            'started_at' => Carbon::parse('2026-03-01 12:00'),
            'ended_at' => Carbon::parse('2026-03-01 13:00'),
        ]);

        $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2026-03-01')
            ->assertOk()
            ->assertSee('2026年3月1日')
            ->assertSee('山田 太郎')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('1:00')
            ->assertSee('8:00')
            ->assertSee("/admin/attendance/{$attendance->id}", false);
    }

    public function test_admin_can_update_attendance_detail(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'work_date' => '2026-03-01',
            'clock_in_at' => Carbon::parse('2026-03-01 09:00'),
            'clock_out_at' => Carbon::parse('2026-03-01 18:00'),
            'note' => '',
        ]);

        $this->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'break1_start' => '13:00',
                'break1_end' => '14:00',
                'break2_start' => '',
                'break2_end' => '',
                'note' => '管理者修正',
            ])
            ->assertRedirect();

        $attendance->refresh()->load('breaks');
        $this->assertSame('10:00', $attendance->clock_in_at?->format('H:i'));
        $this->assertSame('19:00', $attendance->clock_out_at?->format('H:i'));
        $this->assertSame('管理者修正', $attendance->note);
        $this->assertCount(1, $attendance->breaks);
    }

    public function test_admin_can_view_staff_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = User::factory()->create([
            'name' => '佐藤 花子',
            'email' => 'hanako@example.com',
        ]);

        $this->actingAs($admin)
            ->get('/admin/staff/list')
            ->assertOk()
            ->assertSee('佐藤 花子')
            ->assertSee('hanako@example.com')
            ->assertSee("/admin/attendance/staff/{$staff->id}", false);
    }

    public function test_admin_can_view_staff_monthly_attendance_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = User::factory()->create(['name' => '田中 次郎']);
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'work_date' => '2026-03-15',
            'clock_in_at' => Carbon::parse('2026-03-15 09:00'),
            'clock_out_at' => Carbon::parse('2026-03-15 18:00'),
            'note' => '',
        ]);
        $attendance->breaks()->create([
            'started_at' => Carbon::parse('2026-03-15 12:00'),
            'ended_at' => Carbon::parse('2026-03-15 13:00'),
        ]);

        $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}?month=2026-03")
            ->assertOk()
            ->assertSee('田中 次郎さんの勤怠')
            ->assertSee('2026/03')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('1:00')
            ->assertSee('8:00')
            ->assertSee("/admin/attendance/{$attendance->id}", false);
    }

    public function test_admin_can_export_staff_monthly_csv(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'work_date' => '2026-03-15',
            'clock_in_at' => Carbon::parse('2026-03-15 09:00'),
            'clock_out_at' => Carbon::parse('2026-03-15 18:00'),
            'note' => '',
        ]);
        $attendance->breaks()->create([
            'started_at' => Carbon::parse('2026-03-15 12:00'),
            'ended_at' => Carbon::parse('2026-03-15 13:00'),
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}/csv?month=2026-03");

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=staff_'.$staff->id.'_202603.csv');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('日付', $csv);
        $this->assertStringContainsString('2026/03/15', $csv);
        $this->assertStringContainsString('09:00', $csv);
        $this->assertStringContainsString('18:00', $csv);
    }

    public function test_admin_can_view_request_lists_by_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = User::factory()->create(['name' => '申請 太郎']);
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'work_date' => '2026-03-01',
            'clock_in_at' => Carbon::parse('2026-03-01 09:00'),
            'clock_out_at' => Carbon::parse('2026-03-01 18:00'),
            'note' => '',
        ]);
        StampCorrectionRequest::query()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $staff->id,
            'requested_clock_in_at' => Carbon::parse('2026-03-01 10:00'),
            'requested_clock_out_at' => Carbon::parse('2026-03-01 19:00'),
            'requested_break_times' => [],
            'note' => '遅延のため',
            'status' => 'pending',
            'requested_at' => now(),
        ]);
        StampCorrectionRequest::query()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $staff->id,
            'requested_clock_in_at' => Carbon::parse('2026-03-01 08:30'),
            'requested_clock_out_at' => Carbon::parse('2026-03-01 17:30'),
            'requested_break_times' => [],
            'note' => '既に承認済みの申請',
            'status' => 'approved',
            'requested_at' => now()->subDay(),
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/stamp_correction_request/list?status=pending')
            ->assertOk()
            ->assertSee('申請 太郎')
            ->assertSee('遅延のため')
            ->assertDontSee('既に承認済みの申請');

        $this->actingAs($admin)
            ->get('/stamp_correction_request/list?status=approved')
            ->assertOk()
            ->assertSee('既に承認済みの申請');
    }

    public function test_admin_can_approve_stamp_correction_request(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@example.com',
        ]);
        $user = User::factory()->create();
        $date = Carbon::parse('2026-03-01 09:00:00');

        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => $date->toDateString(),
            'clock_in_at' => $date->copy(),
            'clock_out_at' => $date->copy()->setTime(18, 0),
            'note' => '',
        ]);

        $requestItem = StampCorrectionRequest::query()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => $date->copy()->setTime(10, 0),
            'requested_clock_out_at' => $date->copy()->setTime(19, 0),
            'requested_break_times' => [
                ['start' => '13:00', 'end' => '14:00'],
            ],
            'note' => '通院のため',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post("/stamp_correction_request/approve/{$requestItem->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $requestItem->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'note' => '通院のため',
        ]);
    }
}
