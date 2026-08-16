<?php

namespace App\Services\Operations;

use App\DTOs\UserDTO;
use App\Models\User;
use App\Notifications\AccountStatusChangedNotification;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    public function getUserProfile(): User
    {
        return auth()->user();
    }

    public function getUserById(User $user): User
    {
        return $user;
    }

    /**
     * Activate or deactivate a user account. Prevents an admin from
     * deactivating their own account and locking themselves out.
     */
    public function setActiveStatus(User $user, bool $isActive): User
    {
        if (! $isActive && $user->id === auth()->id()) {
            throw ValidationException::withMessages([
                'user' => [__('You cannot deactivate your own account.')],
            ]);
        }

        $statusChanged = (bool) $user->is_active !== $isActive;

        $user->update(['is_active' => $isActive]);

        // Only notify the user when their status actually changed.
        if ($statusChanged) {
            $user->notify(new AccountStatusChangedNotification($isActive));
        }

        return $user->refresh();
    }

    public function updateUserProfile(UserDTO $DTO): array
    {
        return DB::transaction(function () use ($DTO) {
            // Owner-only: a user can edit nothing but their own authenticated profile.
            $user = $this->userRepository->update($DTO, auth()->user());

            return ['user' => $user];
        });
    }
}
