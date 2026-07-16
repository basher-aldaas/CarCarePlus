<?php

namespace App\Repositories\Eloquent;

use App\DTOs\UserDTO;
use App\Models\User;

class UserRepository
{
    public function create(UserDTO $DTO) : User
    {
        $data = $DTO->toArray();
        if(isset($data['password']))
        {
            $data['password'] = bcrypt($data['password']);
        }
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function update(UserDTO $DTO, User $user): User
    {
        $data = $DTO->toArray();
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);

        return $user->refresh();
    }
}
