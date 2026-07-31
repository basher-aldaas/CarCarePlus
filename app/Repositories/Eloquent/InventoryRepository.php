<?php

namespace App\Repositories\Eloquent;

use App\DTOs\InventoryDTO;
use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository
{
    public function getAll(): Collection
    {
        return Inventory::with(['branch', 'material'])->get();
    }

    public function getAllInAdminBranch(): Collection
    {
        $user = auth()->user();
        $branchIds = Branch::query()->where('admin_id', $user->id)->pluck('id');
        return Inventory::whereIn('branch_id', $branchIds)
            ->with(['branch', 'material'])
            ->get();
    }

    public function findById(Inventory $inventory): Inventory
    {
        return $inventory->load(['branch', 'material']);
    }

    public function create(InventoryDTO $dto): Inventory
    {
        return Inventory::create($dto->toArray());
    }

    public function update(Inventory $inventory, InventoryDTO $dto): Inventory
    {
        $inventory->update($dto->toArray());
        return $inventory->fresh(['branch', 'material']);
    }

    public function delete(Inventory $inventory): bool
    {
        return $inventory->delete();
    }

    public function firstOrCreateForBranchMaterial(int $branchId, int $materialId): Inventory
    {
        $inventory = Inventory::where('branch_id', $branchId)
            ->where('material_id', $materialId)
            ->lockForUpdate()
            ->first();

        return $inventory ?? Inventory::create([
            'branch_id' => $branchId,
            'material_id' => $materialId,
            'quantity' => 0,
            'min_quantity' => 0,
        ]);
    }
}
