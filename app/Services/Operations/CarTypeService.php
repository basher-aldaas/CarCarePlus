<?php

namespace App\Services\Operations;

use App\DTOs\CarTypeDTO;
use App\Models\CarType;
use App\Repositories\Eloquent\CarTypeRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CarTypeService
{
    public function __construct(
        protected CarTypeRepository $carTypeRepository
    ) {
    }

    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->carTypeRepository->getAll($perPage);
    }

    public function show(int $id): CarType
    {
        return $this->carTypeRepository->findById($id);
    }

    public function store(CarTypeDTO $dto): CarType
    {
        return DB::transaction(function () use ($dto) {
            return $this->carTypeRepository->create($dto);
        });
    }

    public function update(CarType $carType, CarTypeDTO $dto): CarType
    {
        return DB::transaction(function () use ($carType, $dto) {
            return $this->carTypeRepository->update($carType, $dto);
        });
    }

    public function destroy(CarType $carType): bool
    {
        return DB::transaction(function () use ($carType) {
            return $this->carTypeRepository->delete($carType);
        });
    }
}