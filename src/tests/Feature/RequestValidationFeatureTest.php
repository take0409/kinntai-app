<?php

namespace Tests\Feature;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RequestValidationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_attendance_list_ignores_invalid_month_query(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/attendance/list?month=2026-13')
            ->assertOk()
            ->assertSee('勤怠一覧');
    }

    public function test_admin_attendance_list_ignores_invalid_date_query(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2026-99-99')
            ->assertOk()
            ->assertSee('勤怠一覧');
    }

    public function test_stamp_correction_list_ignores_invalid_status_query(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=waiting')
            ->assertOk()
            ->assertSee('承認待ち');
    }

    public function test_admin_staff_monthly_pages_ignore_invalid_month_query(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}?month=2026-14")
            ->assertOk()
            ->assertSee($staff->name.'さんの勤怠');
    }

    public function test_admin_cannot_open_staff_monthly_page_for_admin_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$otherAdmin->id}")
            ->assertForbidden();
    }

    public function test_update_user_password_uses_form_request_validation_rules(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $action = new UpdateUserPassword;

        $this->expectException(ValidationException::class);

        $action->update($user, [
            'current_password' => 'wrong-password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
    }

    public function test_reset_user_password_uses_form_request_validation_rules(): void
    {
        $user = User::factory()->create();
        $action = new ResetUserPassword;

        $this->expectException(ValidationException::class);

        $action->reset($user, [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
    }

    public function test_update_user_profile_information_uses_form_request_validation_rules(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'duplicate@example.com']);
        $action = new UpdateUserProfileInformation;

        $this->expectException(ValidationException::class);

        $action->update($user, [
            'name' => '',
            'email' => 'duplicate@example.com',
        ]);
    }

    public function test_stamp_correction_approval_request_requires_admin_user(): void
    {
        $staff = User::factory()->create(['is_admin' => false]);
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
            'note' => '通常勤務',
        ]);
        $correctionRequest = StampCorrectionRequest::query()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $staff->id,
            'requested_clock_in_at' => now()->setTime(9, 0),
            'requested_clock_out_at' => now()->setTime(18, 0),
            'requested_break_times' => [],
            'note' => '修正申請',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $this->actingAs($staff)
            ->post("/stamp_correction_request/approve/{$correctionRequest->id}")
            ->assertForbidden();
    }
}
