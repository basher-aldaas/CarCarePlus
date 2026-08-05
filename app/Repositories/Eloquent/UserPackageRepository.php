<?php

namespace App\Repositories\Eloquent;

use App\DTOs\UserPackageDTO;
use App\Enums\UserPackageStatus;
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

    /**
     * The customer's active, unexpired subscription that covers the given
     * service, if any — used to validate a "pay with package" booking.
     */
    public function findActiveCoveringService(int $customerId, int $serviceId): ?UserPackage
    {
        return UserPackage::with('package')
            ->where('user_id', $customerId)
            ->where('status', UserPackageStatus::ACTIVE)
            ->where('end_date', '>=', now())
            ->whereHas('package.packageServices', fn ($query) => $query->where('service_id', $serviceId))
            ->first();
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