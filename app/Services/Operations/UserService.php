<?php

namespace App\Services\Operations;

use App\DTOs\UserDTO;
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

    public function updateUserProfile(UserDTO $DTO): User
    {
        return DB::transaction(function () use ($DTO) {
            // Owner-only: a user can edit nothing but their own authenticated profile.
            $user = $this->userRepository->update($DTO, auth()->user());

            return ['user' => $user];
        });
    }
}
