<?php

namespace App\Repositories\Eloquent;

use App\DTOs\SubServiceDTO;
use App\Models\SubService;

class SubServiceRepository
{
    public function getAll()
    {
        return SubService::with('service')
            ->latest('id')
            ->get();
    }

    public function findById(int $id): SubService
    {
        return SubService::with('service')
            ->findOrFail($id);
    }

    public function create(SubServiceDTO $dto): SubService
    {
        return SubService::create($dto->toArray());
    }

    public function update(SubService $subService, SubServiceDTO $dto): SubService
    {
        $subService->update($dto->toArray());

        return $subService->fresh();
    }

    public function delete(SubService $subService): bool
    {
        return $subService->delete();
    }
}