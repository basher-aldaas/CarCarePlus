<?php

namespace App\Repositories\Eloquent;

use App\DTOs\MaterialUnitDTO;
use App\Models\MaterialUnit;
use Illuminate\Pagination\LengthAwarePaginator;

class MaterialUnitRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return MaterialUnit::paginate($perPage);
    }

    public function findById(MaterialUnit $materialUnit): MaterialUnit
    {
        return $materialUnit;
    }

    public function create(MaterialUnitDTO $dto): MaterialUnit
    {
        return MaterialUnit::create($dto->toArray());
    }

    public function update(MaterialUnit $materialUnit, MaterialUnitDTO $dto): MaterialUnit
    {
        $materialUnit->update($dto->toArray());
        return $materialUnit->fresh();
    }

    public function delete(MaterialUnit $materialUnit): bool
    {
        return $materialUnit->delete();
    }
}
