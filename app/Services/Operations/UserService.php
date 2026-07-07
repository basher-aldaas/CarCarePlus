<?php

namespace App\Services\Operations;

use App\DTOs\OperationsDTO\EditProfileDTO;
use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    public function getUserProfile(): User
    {
        return auth()->user();
    }

    public function editUserProfile(EditProfileDTO $DTO): array
    {
        return DB::transaction(function () use ($DTO) {
            // Owner-only: a user can edit nothing but their own authenticated profile.
            $user = $this->userRepository->update(auth()->user(), $DTO->user);

            return ['user' => $user];
        });
    }
}
