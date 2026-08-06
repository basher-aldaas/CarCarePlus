<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PackageServiceSubServiceDTO;
use App\Models\PackageServiceSubService;
use Illuminate\Pagination\LengthAwarePaginator;

class PackageServiceSubServiceRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return PackageServiceSubService::with(['packageService', 'subService'])
            ->latest('id')
            ->paginate($perPage);
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
