<?php

namespace App\Services\Auth;

use App\DTOs\AuhDTOs\LoginDTO;
use App\Exceptions\AccountInactiveException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\TooManyLoginAttemptsException;
use App\Models\User;
use App\Notifications\LoginNotification;
use App\Notifications\LogoutNotification;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthService
{
    /** Max failed login attempts before the account is temporarily locked. */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /** How long (seconds) the lock lasts once the limit is reached. */
    private const LOCKOUT_SECONDS = 60;

    public function __construct(protected UserRepository $userRepository) {}

    /**
     * Authenticate a user and issue a personal access token.
     *
     * @return array{user: User, token: string}
     *
     * @throws InvalidCredentialsException
     * @throws AccountInactiveException
     */
    public function login(LoginDTO $dto, ?string $ip = null, ?string $userAgent = null): array
    {
        $throttleKey = $this->throttleKey($dto->email, $ip);

        // Block further attempts once the limit is hit, until the lock expires.
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            throw new TooManyLoginAttemptsException(RateLimiter::availableIn($throttleKey));
        }

        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            // Count this failed attempt; it decays after the lockout window.
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

            throw new InvalidCredentialsException();
        }

        if (! $user->is_active) {
            throw new AccountInactiveException();
        }

        // Successful login: reset the failed-attempt counter.
        RateLimiter::clear($throttleKey);

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->notify(new LoginNotification($ip, $userAgent, now()->toDayDateTimeString()));

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Revoke the access token used for the current request.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();

        $user->notify(new LogoutNotification());
    }

    /**
     * Rate-limit key for login attempts, scoped to the email + client IP so a
     * failing attacker cannot lock out an unrelated user from another address.
     */
    private function throttleKey(string $email, ?string $ip): string
    {
        return 'login:' . Str::lower($email) . '|' . ($ip ?? 'unknown');
    }
}
