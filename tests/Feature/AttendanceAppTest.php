<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_validation_messages_are_displayed_in_japanese(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
            'email' => 'メールアドレスを入力してください',
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_staff_can_clock_in_from_attendance_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('勤務外');

        $this->actingAs($user)
            ->post('/attendance/clock-in')
            ->assertRedirect();

        $attendance = Attendance::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($attendance);
        $this->assertSame(now()->toDateString(), $attendance->work_date->toDateString());
    }

    public function test_unregistered_login_shows_expected_error_message(): void
    {
        $response = $this->post('/login', [
            'email' => 'missing@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
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

    public function test_staff_credentials_are_rejected_on_admin_login(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email' => 'staff@example.com',
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
        $this->assertGuest();
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
}
