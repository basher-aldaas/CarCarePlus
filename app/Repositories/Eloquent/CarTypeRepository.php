<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CarTypeDTO;
use App\Models\CarType;

class CarTypeRepository
{
    public function getAll()
    {
        return CarType::latest()->get();
    }

    public function findById(int $id): CarType
    {
        return CarType::findOrFail($id);
    }

    public function create(CarTypeDTO $dto): CarType
    {
        return CarType::create($dto->toArray());
    }

    public function update(CarType $carType, CarTypeDTO $dto): CarType
    {
        $carType->update($dto->toArray());

        return $carType->fresh();
    }

    public function delete(CarType $carType): bool
    {
        return $carType->delete();
    }
}