<?php

namespace App\Services\Operations;

use App\DTOs\PackageServiceDTO;
use App\Models\PackageService as PackageServiceModel;
use App\Repositories\Eloquent\PackageServiceRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PackageServiceService
{
    public function __construct(
        protected PackageServiceRepository $packageServiceRepository
    ) {
    }

    public function index(): Collection
    {
        return $this->packageServiceRepository->getAll();
    }

    public function show(int $id): PackageServiceModel
    {
        return $this->packageServiceRepository->findById($id);
    }

    public function store(PackageServiceDTO $dto): PackageServiceModel
    {
        return DB::transaction(function () use ($dto) {
            return $this->packageServiceRepository->create($dto);
        });
    }

    public function update(PackageServiceModel $packageService, PackageServiceDTO $dto): PackageServiceModel
    {
        return DB::transaction(function () use ($packageService, $dto) {
            return $this->packageServiceRepository->update($packageService, $dto);
        });
    }

    public function destroy(PackageServiceModel $packageService): bool
    {
        return DB::transaction(function () use ($packageService) {
            return $this->packageServiceRepository->delete($packageService);
        });
    }
}