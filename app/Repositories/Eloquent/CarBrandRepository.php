<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CarBrandDTO;
use App\Models\CarBrand;

class CarBrandRepository
{
    public function getAll()
    {
        return CarBrand::latest()->get();
    }

    public function findById(int $id): CarBrand
    {
        return CarBrand::findOrFail($id);
    }

    public function create(CarBrandDTO $dto): CarBrand
    {
        return CarBrand::create($dto->toArray());
    }

    public function update(CarBrand $carBrand, CarBrandDTO $dto): CarBrand
    {
        $carBrand->update($dto->toArray());

        return $carBrand->fresh();
    }

    public function delete(CarBrand $carBrand): bool
    {
        return $carBrand->delete();
    }
}