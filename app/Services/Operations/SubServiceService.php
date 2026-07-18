<?php

namespace App\Services\Operations;

use App\DTOs\SubServiceDTO;
use App\Models\SubService;
use App\Repositories\Eloquent\SubServiceRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SubServiceService
{
    public function __construct(
        protected SubServiceRepository $subServiceRepository
    ) {
    }

    public function index(): Collection
    {
        return $this->subServiceRepository->getAll();
    }

    public function show(int $id): SubService
    {
        return $this->subServiceRepository->findById($id);
    }

    public function store(SubServiceDTO $dto): SubService
    {
        return DB::transaction(function () use ($dto) {
            return $this->subServiceRepository->create($dto);
        });
    }

    public function update(SubService $subService, SubServiceDTO $dto): SubService
    {
        return DB::transaction(function () use ($subService, $dto) {
            return $this->subServiceRepository->update($subService, $dto);
        });
    }

    public function destroy(SubService $subService): bool
    {
        return DB::transaction(function () use ($subService) {
            return $this->subServiceRepository->delete($subService);
        });
    }
}