<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StaffAttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_clock_in_from_attendance_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('勤務外')
            ->assertSee('出勤');

        $this->actingAs($user)
            ->post('/attendance/clock-in')
            ->assertRedirect();

        $attendance = Attendance::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($attendance);
        $this->assertSame(now()->toDateString(), $attendance->work_date->toDateString());
    }

    public function test_attendance_page_displays_current_date_and_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-01 08:00', config('app.timezone')));
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('2026年3月1日(日)')
            ->assertSee('08:00');

        Carbon::setTestNow();
    }

    public function test_attendance_page_shows_working_status_buttons(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-01 10:00', config('app.timezone')));
        $user = User::factory()->create();
        Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => null,
            'note' => '',
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('出勤中')
            ->assertSee('退勤')
            ->assertSee('休憩入');

        Carbon::setTestNow();
    }

    public function test_staff_can_start_and_end_break(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-01 10:00', config('app.timezone')));
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => null,
            'note' => '',
        ]);

        $this->actingAs($user)
            ->post('/attendance/break-start')
            ->assertRedirect();

        $attendance->refresh()->load('breaks');
        $this->assertCount(1, $attendance->breaks);
        $this->assertNull($attendance->breaks->first()->ended_at);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩中')
            ->assertSee('休憩戻');

        $this->actingAs($user)
            ->post('/attendance/break-end')
            ->assertRedirect();

        $attendance->refresh()->load('breaks');
        $this->assertNotNull($attendance->breaks->first()->ended_at);

        Carbon::setTestNow();
    }

    public function test_staff_can_clock_out_when_not_on_break(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-01 18:00', config('app.timezone')));
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => null,
            'note' => '',
        ]);

        $this->actingAs($user)
            ->post('/attendance/clock-out')
            ->assertRedirect();

        $this->assertNotNull($attendance->fresh()->clock_out_at);
        Carbon::setTestNow();
    }

    public function test_clock_out_is_ignored_while_on_break(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => null,
            'note' => '',
        ]);
        $attendance->breaks()->create([
            'started_at' => now()->setTime(12, 0),
            'ended_at' => null,
        ]);

        $this->actingAs($user)
            ->post('/attendance/clock-out')
            ->assertRedirect();

        $this->assertNull($attendance->fresh()->clock_out_at);
    }

    public function test_finished_attendance_page_shows_done_message(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-01 19:00', config('app.timezone')));
        $user = User::factory()->create();
        Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
            'note' => '',
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('退勤済')
            ->assertSee('お疲れ様でした。');

        Carbon::setTestNow();
    }

    public function test_staff_can_view_monthly_attendance_list_and_move_months(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-15',
            'clock_in_at' => Carbon::parse('2026-03-15 09:00'),
            'clock_out_at' => Carbon::parse('2026-03-15 18:00'),
            'note' => '',
        ]);
        $attendance->breaks()->create([
            'started_at' => Carbon::parse('2026-03-15 12:00'),
            'ended_at' => Carbon::parse('2026-03-15 13:00'),
        ]);

        $this->actingAs($user)
            ->get('/attendance/list?month=2026-03')
            ->assertOk()
            ->assertSee('2026/03')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('1:00')
            ->assertSee('8:00')
            ->assertSee("/attendance/detail/{$attendance->id}", false);

        $this->actingAs($user)
            ->get('/attendance/list?month=2026-02')
            ->assertOk()
            ->assertSee('2026/02');
    }

    public function test_staff_can_only_view_own_attendance_detail(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $otherUser->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
            'note' => '',
        ]);

        $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}")
            ->assertNotFound();
    }

    public function test_attendance_detail_validation_messages_are_displayed_in_japanese(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
            'note' => '',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'clock_in' => '',
            'clock_out' => '16:53',
            'break1_start' => '',
            'break1_end' => '16:53',
            'break2_start' => '1',
            'break2_end' => '',
            'note' => '',
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間を入力してください',
            'break1_start' => '休憩時間が不適切な値です',
            'note' => '備考を入力してください',
        ]);
    }

    public function test_attendance_detail_invalid_time_does_not_raise_server_error(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
            'note' => '',
        ]);

        $response = $this->actingAs($user)->from("/attendance/detail/{$attendance->id}")
            ->post("/attendance/detail/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break1_start' => '',
                'break1_end' => '',
                'break2_start' => '1',
                'break2_end' => '',
                'note' => '確認',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('break2_start');
    }

    public function test_staff_can_submit_correction_request_and_view_it_in_pending_list(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-10',
            'clock_in_at' => Carbon::parse('2026-03-10 09:00'),
            'clock_out_at' => Carbon::parse('2026-03-10 18:00'),
            'note' => '',
        ]);

        $this->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'break1_start' => '13:00',
                'break1_end' => '14:00',
                'break2_start' => '',
                'break2_end' => '',
                'note' => '通院のため',
            ])
            ->assertRedirect('/stamp_correction_request/list');

        $requestItem = StampCorrectionRequest::query()->first();

        $this->assertNotNull($requestItem);
        $this->assertSame('pending', $requestItem->status);

        $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=pending')
            ->assertOk()
            ->assertSee('承認待ち')
            ->assertSee('通院のため')
            ->assertSee("/attendance/detail/{$attendance->id}", false);
    }

    public function test_pending_request_prevents_staff_from_submitting_another_correction(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-10',
            'clock_in_at' => Carbon::parse('2026-03-10 09:00'),
            'clock_out_at' => Carbon::parse('2026-03-10 18:00'),
            'note' => '',
        ]);
        StampCorrectionRequest::query()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => Carbon::parse('2026-03-10 10:00'),
            'requested_clock_out_at' => Carbon::parse('2026-03-10 19:00'),
            'requested_break_times' => [],
            'note' => '通院のため',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'break1_start' => '',
                'break1_end' => '',
                'break2_start' => '',
                'break2_end' => '',
                'note' => '再申請',
            ]);

        $response->assertSessionHasErrors([
            'attendance' => '承認待ちのため修正はできません。',
        ]);
        $this->assertSame(1, StampCorrectionRequest::query()->count());
    }
}
