<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function user(): User
    {
        return User::factory()->create([
            'email'     => 'user@example.com',
            'password'  => Hash::make('oldpassword'),
            'is_active' => true,
        ]);
    }

    // ---- Forgot password ----

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->postJson('/api/auth/forgot-password', ['email' => 'user@example.com'])
            ->assertOk()
            ->assertJsonPath('status', 1);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_is_generic_for_unknown_email(): void
    {
        Notification::fake();

        // Does not reveal whether the email exists.
        $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@example.com'])
            ->assertOk()
            ->assertJsonPath('status', 1);

        Notification::assertNothingSent();
    }

    // ---- Reset password ----

    public function test_user_can_reset_password_with_valid_token(): void
    {
        Notification::fake();
        $user = $this->user();
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'email'                 => 'user@example.com',
            'token'                 => $token,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk()->assertJsonPath('status', 1);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $this->user();

        $this->postJson('/api/auth/reset-password', [
            'email'                 => 'user@example.com',
            'token'                 => 'totally-wrong-token',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(400)->assertJsonPath('status', 0);
    }

    public function test_reset_password_revokes_existing_tokens(): void
    {
        $user = $this->user();
        $user->createToken('device-1');
        $user->createToken('device-2');
        $this->assertCount(2, $user->tokens()->get());

        $token = Password::createToken($user);
        $this->postJson('/api/auth/reset-password', [
            'email'                 => 'user@example.com',
            'token'                 => $token,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->assertCount(0, $user->fresh()->tokens()->get());
    }
}
