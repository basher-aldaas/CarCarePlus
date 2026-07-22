<?php

namespace App\Repositories\Eloquent;

use App\DTOs\UserPackageDTO;
use App\Models\UserPackage;
use Illuminate\Database\Eloquent\Collection;

class UserPackageRepository
{
    public function getByCustomerId(int $customer_id): Collection
    {
        return UserPackage::with('package')
            ->where('user_id', $customer_id)
            ->latest('id')
            ->get();
    }

    public function findById(int $id): UserPackage
    {
        return UserPackage::with(['user', 'package'])
            ->findOrFail($id);
    }

    public function create(UserPackageDTO $dto): UserPackage
    {
        return UserPackage::create($dto->toArray());
    }

    public function update(UserPackage $userPackage, UserPackageDTO $dto): UserPackage
    {
        $userPackage->update($dto->toArray());

        return $userPackage->fresh();
    }

    public function delete(UserPackage $userPackage): bool
    {
        return $userPackage->delete();
    }
}