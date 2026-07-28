<?php

namespace App\Services\Operations;

use App\DTOs\UserPackageDTO;
use App\Enums\UserPackageStatus;
use App\Models\UserPackage;
use App\Repositories\Eloquent\PackageRepository;
use App\Repositories\Eloquent\UserPackageRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserPackageService
{
    public function __construct(
        protected UserPackageRepository $userPackageRepository,
        protected PackageRepository $packageRepository
    ) {
    }

    public function index(int $customer_id): Collection
    {
        return $this->userPackageRepository->getByCustomerId($customer_id);
    }

    public function show(int $id): UserPackage
    {
        return $this->userPackageRepository->findById($id);
    }

    public function store(UserPackageDTO $dto): UserPackage
    {
        return DB::transaction(function () use ($dto) {
            $package = $this->packageRepository->findById($dto->package_id);

            $dto->start_date = now()->toDateString();
            $dto->end_date = now()->addDays($package->valid_days)->toDateString();
            $dto->remaining_count = $package->services_count;
            $dto->status = UserPackageStatus::ACTIVE->value;

            return $this->userPackageRepository->create($dto);
        });
    }

    public function update(UserPackage $userPackage, UserPackageDTO $dto): UserPackage
    {
        return DB::transaction(function () use ($userPackage, $dto) {
            return $this->userPackageRepository->update($userPackage, $dto);
        });
    }

    public function destroy(UserPackage $userPackage): bool
    {
        return DB::transaction(function () use ($userPackage) {
            return $this->userPackageRepository->delete($userPackage);
        });
    }
}
