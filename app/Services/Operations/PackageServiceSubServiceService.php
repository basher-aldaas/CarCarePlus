<?php

namespace App\Services\Operations;

use App\DTOs\PackageServiceSubServiceDTO;
use App\Models\PackageServiceSubService;
use App\Repositories\Eloquent\PackageServiceSubServiceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PackageServiceSubServiceService
{
    public function __construct(
        protected PackageServiceSubServiceRepository $packageServiceSubServiceRepository
    ) {
    }

    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->packageServiceSubServiceRepository->getAll($perPage);
    }

    public function show(int $id): PackageServiceSubService
    {
        return $this->packageServiceSubServiceRepository->findById($id);
    }

    public function store(PackageServiceSubServiceDTO $dto): PackageServiceSubService
    {
        return DB::transaction(function () use ($dto) {
            return $this->packageServiceSubServiceRepository->create($dto);
        });
    }

    public function update(PackageServiceSubService $packageServiceSubService, PackageServiceSubServiceDTO $dto): PackageServiceSubService
    {
        return DB::transaction(function () use ($packageServiceSubService, $dto) {
            return $this->packageServiceSubServiceRepository->update($packageServiceSubService, $dto);
        });
    }

    public function destroy(PackageServiceSubService $packageServiceSubService): bool
    {
        return DB::transaction(function () use ($packageServiceSubService) {
            return $this->packageServiceSubServiceRepository->delete($packageServiceSubService);
        });
    }
}