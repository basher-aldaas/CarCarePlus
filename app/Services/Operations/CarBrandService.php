<?php

namespace App\Services\Operations;

use App\DTOs\CarBrandDTO;
use App\Models\CarBrand;
use App\Repositories\Eloquent\CarBrandRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CarBrandService
{
    public function __construct(
        protected CarBrandRepository $carBrandRepository
    ) {
    }

    public function index(): Collection
    {
        return $this->carBrandRepository->getAll();
    }

    public function show(int $id): CarBrand
    {
        return $this->carBrandRepository->findById($id);
    }

    public function store(CarBrandDTO $dto): CarBrand
    {
        return DB::transaction(function () use ($dto) {
            return $this->carBrandRepository->create($dto);
        });
    }

    public function update(CarBrand $carBrand, CarBrandDTO $dto): CarBrand
    {
        return DB::transaction(function () use ($carBrand, $dto) {
            return $this->carBrandRepository->update($carBrand, $dto);
        });
    }

    public function destroy(CarBrand $carBrand): bool
    {
        return DB::transaction(function () use ($carBrand) {
            return $this->carBrandRepository->delete($carBrand);
        });
    }
}