<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PackageServiceSubServiceDTO;
use App\Models\PackageServiceSubService;

class PackageServiceSubServiceRepository
{
    public function getAll()
    {
        return PackageServiceSubService::with(['packageService', 'subService'])
            ->latest('id')
            ->get();
    }

    public function findById(int $id): PackageServiceSubService
    {
        return PackageServiceSubService::with(['packageService', 'subService'])
            ->findOrFail($id);
    }

    public function create(PackageServiceSubServiceDTO $dto): PackageServiceSubService
    {
        return PackageServiceSubService::create($dto->toArray());
    }

    public function update(PackageServiceSubService $packageServiceSubService, PackageServiceSubServiceDTO $dto): PackageServiceSubService
    {
        $packageServiceSubService->update($dto->toArray());

        return $packageServiceSubService->fresh();
    }

    public function delete(PackageServiceSubService $packageServiceSubService): bool
    {
        return $packageServiceSubService->delete();
    }
}
