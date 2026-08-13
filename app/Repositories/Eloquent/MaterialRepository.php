<?php

namespace App\Repositories\Eloquent;

use App\DTOs\MaterialDTO;
use App\Models\Material;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MaterialRepository
{
    public function getAll(int $perPage = 15, bool $onlyVisibleToCustomer = false): LengthAwarePaginator
    {
        return Material::with('unit')
            ->when($onlyVisibleToCustomer, fn ($query) => $query->where('is_visible_to_customer', true))
            ->paginate($perPage);
    }

    public function findManyByIds(array $ids): Collection
    {
        return Material::whereIn('id', $ids)->get()->keyBy('id');
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
