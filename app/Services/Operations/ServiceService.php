<?php


namespace App\Services\Operations;

use App\DTOs\ServiceDTO;
use App\Models\Service;
use App\Repositories\Eloquent\ServiceRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    public function __construct(
        protected ServiceRepository $serviceRepository
    )
    {
    }

    public function index(): Collection
    {
        return $this->serviceRepository->getAll();
    }

    public function show(int $id): Service
    {
        return $this->serviceRepository->findById($id);
    }

    public function store(ServiceDTO $dto): Service
    {
        return DB::transaction(function () use ($dto) {

            return $this->serviceRepository->create($dto);

        });
    }

    public function update(
        Service    $service,
        ServiceDTO $dto
    ): Service
    {

        return DB::transaction(function () use ($service, $dto) {

            return $this->serviceRepository
                ->update($service, $dto);

        });
    }

    public function destroy(Service $service): bool
    {
        return DB::transaction(function () use ($service) {

            return $this->serviceRepository
                ->delete($service);

        });
    }
}
