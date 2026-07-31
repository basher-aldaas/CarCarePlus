<?php

namespace App\Repositories\Eloquent;

use App\DTOs\InventoryTransactionDTO;
use App\Models\Branch;
use App\Models\InventoryTransaction;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InventoryTransactionRepository
{
    public function getAll(): Collection
    {
        return InventoryTransaction::with(['branch', 'destinationBranch', 'material', 'creator'])
            ->latest('id')
            ->get();
    }

    public function getAllInAdminBranch(): Collection
    {
        $user = auth()->user();
        $branchIds = Branch::query()->where('admin_id', $user->id)->pluck('id');
        return InventoryTransaction::whereIn('branch_id', $branchIds)
            ->with(['branch', 'destinationBranch', 'material', 'creator'])
            ->latest('id')
            ->get();
    }

    public function findById(InventoryTransaction $inventoryTransaction): InventoryTransaction
    {
        return $inventoryTransaction->load(['branch', 'destinationBranch', 'material', 'creator']);
    }

    public function findByIdForAdmin(InventoryTransaction $inventoryTransaction): InventoryTransaction
    {
        $user = auth()->user();

        $inventoryTransaction->loadMissing('branch');

        if ((int) $inventoryTransaction->branch->admin_id !== (int) $user->id) {
            throw new AccessDeniedHttpException('You are not allowed to view this inventory transaction.');
        }

        return $inventoryTransaction->load(['branch', 'destinationBranch', 'material', 'creator']);
    }

    public function create(InventoryTransactionDTO $dto): InventoryTransaction
    {
        return InventoryTransaction::create($dto->toArray());
    }
}
