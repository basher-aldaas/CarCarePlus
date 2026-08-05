<?php

namespace App\Services\Operations;

use App\DTOs\PackageDTO;
use App\Exceptions\PackageViewUnauthorizedException;
use App\Models\Package;
use App\Repositories\Eloquent\PackageRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PackageService
{
    public function __construct(
        protected PackageRepository $packageRepository
    ) {
    }

    /**
     * A customer only sees packages targeted at their own account type
     * (personal vs. company); super admin/admin see every package.
     */
    public function index(): Collection
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $this->packageRepository->getAll();
        }

        return $this->packageRepository->getAllForCustomerType($user->hasRole('customer_company'));
    }

    public function show(int $id): Package
    {
        $package = $this->packageRepository->findById($id);
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $package;
        }

        if ($package->is_company_package !== $user->hasRole('customer_company')) {
            throw new PackageViewUnauthorizedException();
        }

        return $package;
    }

    public function store(PackageDTO $dto): Package
    {
        return DB::transaction(function () use ($dto) {
            return $this->packageRepository->create($dto);
        });
    }

    public function update(Package $package, PackageDTO $dto): Package
    {
        return DB::transaction(function () use ($package, $dto) {
            return $this->packageRepository->update($package, $dto);
        });
    }

    public function destroy(Package $package): bool
    {
        return DB::transaction(function () use ($package) {
            return $this->packageRepository->delete($package);
        });
    }
}