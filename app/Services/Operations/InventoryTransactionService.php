<?php

namespace App\Services\Operations;

use App\DTOs\InventoryTransactionDTO;
use App\Enums\InventoryTransactionType;
use App\Exceptions\InsufficientStockException;
use App\Models\Branch;
use App\Models\InventoryTransaction;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\InventoryTransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InventoryTransactionService
{
    public function __construct(
        protected InventoryTransactionRepository $inventoryTransactionRepository,
        protected InventoryRepository $inventoryRepository,
    ) {}

    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('super_admin')){
            return $this->inventoryTransactionRepository->getAll();
        }
        if ($user->hasRole('admin')){
            return $this->inventoryTransactionRepository->getAllInAdminBranch();
        }

        throw new AccessDeniedHttpException('You are not allowed to view inventory transactions.');
    }

    public function show(InventoryTransaction $inventoryTransaction): InventoryTransaction
    {
        $user = auth()->user();
        if ($user->hasRole('super_admin')){
            return $this->inventoryTransactionRepository->findById($inventoryTransaction);
        }

        if ($user->hasRole('admin')){
            return $this->inventoryTransactionRepository->findByIdForAdmin($inventoryTransaction);
        }

        throw new AccessDeniedHttpException('You are not allowed to view inventory transactions.');

    }

    /**
     * Applies the movement to the branch's Inventory row and records the
     * resulting before/after snapshot as an immutable ledger entry. A
     * "transfer" also books the matching receiving leg on the destination
     * branch, atomically, so both branches' stock stay consistent.
     */
    public function store(array $data, int $createdBy): InventoryTransaction
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $type = InventoryTransactionType::from($data['type']);
            $branchId = $this->resolveBranchId($data);
            $materialId = (int) $data['material_id'];
            $quantity = (float) $data['quantity'];
            if ($type === InventoryTransactionType::TRANSFER_OUT) {
                $destinationBranchId = (int) $data['destination_branch_id'];
            } else {
                $destinationBranchId = null;
            }

            if ($destinationBranchId === $branchId) {
                throw ValidationException::withMessages([
                    'destination_branch_id' => __('The destination branch must be different from the source branch.'),
                ]);
            }

            if ($type === InventoryTransactionType::IN) {
                $delta = $quantity;
            } else {
                $delta = -$quantity;
            }

            $result = $this->applyMovement($branchId, $materialId, $delta);

            $quantityBefore = $result[0];
            $quantityAfter  = $result[1];

            $transaction = $this->inventoryTransactionRepository->create(InventoryTransactionDTO::fromArray([
                'branch_id' => $branchId,
                'destination_branch_id' => $destinationBranchId,
                'material_id' => $materialId,
                'created_by' => $createdBy,
                'type' => $type->value,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_id' => $data['reference_id'] ?? null,
                'note' => $data['note'] ?? null,
            ]));

            if ($type === InventoryTransactionType::TRANSFER_OUT) {
                $result = $this->applyMovement($destinationBranchId, $materialId, $quantity);

                $destinationBefore = $result[0];
                $destinationAfter = $result[1];
                $this->inventoryTransactionRepository->create(InventoryTransactionDTO::fromArray([
                    'branch_id' => $destinationBranchId,
                    'destination_branch_id' => $branchId,
                    'material_id' => $materialId,
                    'created_by' => $createdBy,
                    'type' => InventoryTransactionType::TRANSFER_IN->value,
                    'quantity' => $quantity,
                    'quantity_before' => $destinationBefore,
                    'quantity_after' => $destinationAfter,
                    'reference_id' => $data['reference_id'] ?? null,
                    'note' => $data['note'] ?? null,
                ]));
            }

            return $transaction;
        });
    }

    /**
     * Super admin must name the branch explicitly (validated as required in
     * the request); an admin's transactions always originate from the
     * single branch he manages, so branch_id is derived here and any value
     * he sent is ignored.
     */
    private function resolveBranchId(array $data): int
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return (int) $data['branch_id'];
        }

        if ($user->hasRole('admin')) {
            $branchId = Branch::query()->where('admin_id', $user->id)->value('id');

            if (! $branchId) {
                throw new AccessDeniedHttpException('You are not assigned to a branch.');
            }

            return (int) $branchId;
        }

        throw new AccessDeniedHttpException('You are not allowed to create inventory transactions.');
    }

    /**
     * Adjusts a branch/material's Inventory.quantity by $delta and returns
     * [quantityBefore, quantityAfter]. Refuses to drive stock negative.
     */
    private function applyMovement(int $branchId, int $materialId, float $delta): array
    {
        $inventory = $this->inventoryRepository->firstOrCreateForBranchMaterial($branchId, $materialId);
        $before = (float) $inventory->quantity;
        $after = $before + $delta;

        if ($after < 0) {
            throw new InsufficientStockException();
        }

        $inventory->update(['quantity' => $after]);

        return [$before, $after];
    }
}
