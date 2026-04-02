<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationFeatureTest extends TestCase
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

    public function test_register_sends_verification_email_and_redirects_to_attendance_then_verify_screen(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => '認証 太郎',
            'email' => 'verify@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'verify@example.com')->first();

        $this->assertNotNull($user);
        $response->assertRedirect('/attendance');
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertRedirect('/email/verify');
    }

    public function test_verification_email_can_be_resent(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post('/email/verification-notification')
            ->assertStatus(302);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_verify_email_from_signed_link(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect('/attendance?verified=1');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
