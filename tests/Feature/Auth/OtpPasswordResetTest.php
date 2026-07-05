<?php

namespace Tests\Feature\Auth;

use App\Enums\OtpEnums\OtpChannel;
use App\Enums\OtpEnums\OtpType;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\PasswordResetOtpNotification;
use App\Services\Auth\OtpService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OtpPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Notification::fake();
    }

    private function user(): User
    {
        return User::factory()->create([
            'email'     => 'user@example.com',
            'password'  => Hash::make('oldpassword'),
            'is_active' => true,
        ]);
    }

    /** Seed a real OTP and return the plaintext code. */
    private function seedOtp(User $user): string
    {
        return app(OtpService::class)->generate($user, OtpType::RESET_PASSWORD, OtpChannel::EMAIL);
    }

    public function test_send_otp_emails_a_code(): void
    {
        $user = $this->user();

        $this->postJson('/api/auth/password/otp/send', ['email' => 'user@example.com'])
            ->assertOk()
            ->assertJsonPath('status', 1);

        Notification::assertSentTo($user, PasswordResetOtpNotification::class);
        $this->assertDatabaseCount('otp_codes', 1);
    }

    public function test_send_otp_is_generic_for_unknown_email(): void
    {
        $this->postJson('/api/auth/password/otp/send', ['email' => 'nobody@example.com'])
            ->assertOk()
            ->assertJsonPath('status', 1);

        Notification::assertNothingSent();
        $this->assertDatabaseCount('otp_codes', 0);
    }

    public function test_otp_code_is_stored_hashed_not_plaintext(): void
    {
        $user = $this->user();
        $code = $this->seedOtp($user);

        $stored = OtpCode::first();
        $this->assertNotSame($code, $stored->code);
        $this->assertTrue(Hash::check($code, $stored->code));
    }

    public function test_user_can_reset_password_with_valid_otp(): void
    {
        $user = $this->user();
        $code = $this->seedOtp($user);

        $this->postJson('/api/auth/password/otp/reset', [
            'email'                 => 'user@example.com',
            'otp'                   => $code,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk()->assertJsonPath('status', 1);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        Notification::assertSentTo($user, PasswordChangedNotification::class);
        $this->assertTrue(OtpCode::first()->is_used);
    }

    public function test_reset_fails_with_wrong_otp(): void
    {
        $user = $this->user();
        $this->seedOtp($user);

        $this->postJson('/api/auth/password/otp/reset', [
            'email'                 => 'user@example.com',
            'otp'                   => '000000',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422)->assertJsonPath('status', 0);

        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }

    public function test_reset_fails_with_expired_otp(): void
    {
        $user = $this->user();
        $code = $this->seedOtp($user);
        OtpCode::first()->update(['expires_at' => now()->subMinute()]);

        $this->postJson('/api/auth/password/otp/reset', [
            'email'                 => 'user@example.com',
            'otp'                   => $code,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422);
    }

    public function test_used_otp_cannot_be_reused(): void
    {
        $user = $this->user();
        $code = $this->seedOtp($user);

        $payload = [
            'email'                 => 'user@example.com',
            'otp'                   => $code,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $this->postJson('/api/auth/password/otp/reset', $payload)->assertOk();
        $this->postJson('/api/auth/password/otp/reset', $payload)->assertStatus(422);
    }

    public function test_resending_otp_within_cooldown_is_throttled(): void
    {
        $this->user();

        $this->postJson('/api/auth/password/otp/send', ['email' => 'user@example.com'])->assertOk();
        $this->postJson('/api/auth/password/otp/send', ['email' => 'user@example.com'])
            ->assertStatus(429);
    }
}
