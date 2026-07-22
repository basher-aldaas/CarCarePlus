<?php

namespace App\Services\Operations;

use App\DTOs\PackageDTO;
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

    public function index(): Collection
    {
        return $this->packageRepository->getAll();
    }

    public function show(int $id): Package
    {
        return $this->packageRepository->findById($id);
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