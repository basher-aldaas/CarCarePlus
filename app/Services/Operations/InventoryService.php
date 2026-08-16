<?php

namespace App\Services\Operations;

use App\DTOs\InventoryDTO;
use App\Models\Branch;
use App\Models\Inventory;
use App\Repositories\Eloquent\InventoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InventoryService
{
    public function __construct(protected InventoryRepository $inventoryRepository)
    {}
    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('super_admin')){
            return $this->inventoryRepository->getAll();
        }
        if ($user->hasRole('admin')){
            return $this->inventoryRepository->getAllInAdminBranch();
        }

        throw new AccessDeniedHttpException(__('You are not allowed to view inventory records.'));
    }

    public function show(Inventory $inventory): Inventory
    {
        $user = auth()->user();

        // Super Admin can view any inventory record
        if ($user->hasRole('super_admin')) {
            return $this->inventoryRepository->findById($inventory);
        }

        // Admin can view only inventory records that belong to his own branch
        if ($user->hasRole('admin') && $user->id === $inventory->branch->admin_id) {
            return $this->inventoryRepository->findById($inventory);
        }

        throw new AccessDeniedHttpException(__('You are not allowed to view this inventory record.'));
    }

    public function store(InventoryDTO $dto): Inventory
    {
        return DB::transaction(function () use($dto){
            $user = auth()->user();

            // Admin can create inventory records only for his own branch;
            // Super Admin's submitted branch_id is used as-is.
            if ($user->hasRole('admin')) {
                $branch_id = Branch::where('admin_id', $user->id)->value('id');

                if (! $branch_id) {
                    throw new AccessDeniedHttpException(__('You are not assigned to a branch.'));
                }

                $dto->branch_id = $branch_id;
            } elseif (! $user->hasRole('super_admin')) {
                throw new AccessDeniedHttpException(__('You are not allowed to create inventory records.'));
            }

            $exists = Inventory::where('branch_id', $dto->branch_id)
                ->where('material_id', $dto->material_id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'material_id' => __('An inventory record already exists for this branch and material.'),
                ]);
            }

            return $this->inventoryRepository->create($dto);
        });
    }

    public function update(Inventory $inventory, InventoryDTO $dto): Inventory
    {
        return DB::transaction(function () use ($inventory, $dto){
            $user = auth()->user();

            // Super Admin can update any inventory record
            if ($user->hasRole('super_admin')) {
                return $this->inventoryRepository->update($inventory, $dto);
            }

            // Admin can update only inventory records that belong to his own branch
            if ($user->hasRole('admin') && $user->id === $inventory->branch->admin_id) {
                return $this->inventoryRepository->update($inventory, $dto);
            }

            throw new AccessDeniedHttpException(__('You are not allowed to update this inventory.'));
        });
    }

    public function delete(Inventory $inventory): bool
    {
        return DB::transaction(function () use($inventory){

            $user = auth()->user();

            // Super Admin can delete any inventory record
            if ($user->hasRole('super_admin')) {
                return $this->inventoryRepository->delete($inventory);
            }

            // Admin can delete only inventory records that belong to his own branch
            if ($user->hasRole('admin') && $user->id === $inventory->branch->admin_id) {
                return $this->inventoryRepository->delete($inventory);
            }

            throw new AccessDeniedHttpException(__('You are not allowed to delete this inventory record.'));
        });
    }
}
