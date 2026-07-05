<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginLogoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function activeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email'     => 'user@example.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ], $overrides));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->activeUser();

        $res = $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $res->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.email', 'user@example.com')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertCount(1, $user->tokens()->get());
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->activeUser();

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('status', 0);
    }

    public function test_login_fails_for_unknown_email(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password123',
        ])->assertUnauthorized();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $this->activeUser(['is_active' => false]);

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ])->assertForbidden()
            ->assertJsonPath('status', 0);
    }

    public function test_login_validation_errors(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertStatus(422)
            ->assertJsonPath('status', 0);
    }

    public function test_authenticated_user_can_logout_and_token_is_revoked(): void
    {
        $user = $this->activeUser();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertCount(0, $user->fresh()->tokens()->get());
    }

    public function test_guest_cannot_logout(): void
    {
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }
}
