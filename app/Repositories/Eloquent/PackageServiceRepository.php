<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PackageServiceDTO;
use App\Models\PackageService;

class PackageServiceRepository
{
    public function getAll()
    {
        return PackageService::with(['package', 'service'])
            ->latest('id')
            ->get();
    }

    public function findById(int $id): PackageService
    {
        return PackageService::with(['package', 'service'])
            ->findOrFail($id);
    }

    public function create(PackageServiceDTO $dto): PackageService
    {
        return PackageService::create($dto->toArray());
    }

    public function update(PackageService $packageService, PackageServiceDTO $dto): PackageService
    {
        $packageService->update($dto->toArray());

        return $packageService->fresh();
    }

    public function delete(PackageService $packageService): bool
    {
        return $packageService->delete();
    }
}