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
     * The customer's active, unexpired, unused subscriptions that cover the
     * given service — offered to the customer to choose from before a
     * "pay with package" booking is priced.
     */
    public function findEligible(int $customerId, int $serviceId): Collection
    {
        return UserPackage::with(['package.packageServices.subServiceOverrides'])
            ->where('user_id', $customerId)
            ->where('status', UserPackageStatus::ACTIVE)
            ->where('end_date', '>=', now())
            ->where('remaining_count', '>', 0)
            ->whereHas('package.packageServices', fn ($query) => $query->where('service_id', $serviceId))
            ->get();
    }

    /**
     * A specific subscription, scoped to its owner, with coverage relations
     * eager-loaded — used once the customer has chosen which package to use.
     */
    public function findOwnedActive(int $customerId, int $userPackageId): ?UserPackage
    {
        return UserPackage::with(['package.packageServices.subServiceOverrides'])
            ->where('user_id', $customerId)
            ->find($userPackageId);
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