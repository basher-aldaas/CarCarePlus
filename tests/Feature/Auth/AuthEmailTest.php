<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\LoginNotification;
use App\Notifications\LogoutNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Notification::fake();
    }

    private function activeUser(): User
    {
        return User::factory()->create([
            'email'     => 'user@example.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    public function test_login_sends_login_notification(): void
    {
        $user = $this->activeUser();

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ])->assertOk();

        Notification::assertSentTo($user, LoginNotification::class);
    }

    public function test_failed_login_sends_no_notification(): void
    {
        $this->activeUser();

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong',
        ])->assertUnauthorized();

        Notification::assertNothingSent();
    }

    public function test_logout_sends_logout_notification(): void
    {
        $user = $this->activeUser();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk();

        Notification::assertSentTo($user, LogoutNotification::class);
    }

    /** Content smoke test — templates render and carry the sign-in context. */
    public function test_login_notification_renders_context(): void
    {
        $user = User::factory()->make(['name' => 'Sara']);

        $mail = (new LoginNotification('203.0.113.5', 'Chrome on Windows', 'Sun, Jul 5, 2026 10:00 AM'))
            ->toMail($user);

        $this->assertNotEmpty($mail->subject);
        $this->assertTrue(collect($mail->introLines)->contains(fn ($l) => str_contains($l, '203.0.113.5')));
        $this->assertTrue(collect($mail->introLines)->contains(fn ($l) => str_contains($l, 'Chrome on Windows')));
    }
}
