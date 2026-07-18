<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ServiceDTO;
use App\Models\Service;

class ServiceRepository
{
    public function getAll()
    {
        return Service::with('category')
            ->latest()
            ->get();
    }

    public function findById(int $id): Service
    {
        return Service::with('category')
            ->findOrFail($id);
    }

    public function create(ServiceDTO $dto): Service
    {
        return Service::create($dto->toArray());
    }

    public function update(Service $service, ServiceDTO $dto): Service
    {
        $service->update($dto->toArray());

        return $service->fresh();
    }

    public function delete(Service $service): bool
    {
        return $service->delete();
    }
}
