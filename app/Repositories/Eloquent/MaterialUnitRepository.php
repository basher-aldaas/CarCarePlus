<?php

namespace App\Repositories\Eloquent;

use App\DTOs\MaterialUnitDTO;
use App\Models\MaterialUnit;
use Illuminate\Database\Eloquent\Collection;

class MaterialUnitRepository
{
    public function getAll(): Collection
    {
        return MaterialUnit::get();
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
