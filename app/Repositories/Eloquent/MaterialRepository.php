<?php

namespace App\Repositories\Eloquent;

use App\DTOs\MaterialDTO;
use App\Models\Material;
use Illuminate\Database\Eloquent\Collection;

class MaterialRepository
{
    public function getAll(): Collection
    {
        return Material::with('unit')->get();
    }

    public function findById(Material $material): Material
    {
        return $material->load('unit');
    }

    public function create(MaterialDTO $dto): Material
    {
        return Material::create($dto->toArray());
    }

    public function update(Material $material, MaterialDTO $dto): Material
    {
        $material->update($dto->toArray());
        return $material->fresh('unit');
    }

    public function delete(Material $material): bool
    {
        return $material->delete();
    }
}
