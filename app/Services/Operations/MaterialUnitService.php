<?php

namespace App\Services\Operations;

use App\DTOs\MaterialUnitDTO;
use App\Models\MaterialUnit;
use App\Repositories\Eloquent\MaterialUnitRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MaterialUnitService
{
    public function __construct(protected MaterialUnitRepository $materialUnitRepository)
    {}
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->materialUnitRepository->getAll($perPage);
    }

    public function show(MaterialUnit $materialUnit): MaterialUnit
    {
        return $this->materialUnitRepository->findById($materialUnit);
    }

    public function store(MaterialUnitDTO $dto): MaterialUnit
    {
        return DB::transaction(function () use($dto){
            return $this->materialUnitRepository->create($dto);
        });
    }

    public function update(MaterialUnit $materialUnit, MaterialUnitDTO $dto): MaterialUnit
    {
        return DB::transaction(function () use ($materialUnit, $dto){
            return $this->materialUnitRepository->update($materialUnit, $dto);
        });
    }

    public function delete(MaterialUnit $materialUnit): bool
    {
        return DB::transaction(function () use($materialUnit){
            return $this->materialUnitRepository->delete($materialUnit);
        });
    }
}
