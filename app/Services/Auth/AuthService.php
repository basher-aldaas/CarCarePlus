<?php

namespace App\Services\Auth;

use App\DTOs\AuthDTOs\LoginDTO;
use App\Exceptions\AccountInactiveException;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Notifications\LoginNotification;
use App\Notifications\LogoutNotification;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
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
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        if (! $user->is_active) {
            throw new AccountInactiveException();
        }

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
}
