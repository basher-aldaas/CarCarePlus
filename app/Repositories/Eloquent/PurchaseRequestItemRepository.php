<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PurchaseRequestItemDTO;
use App\Models\PurchaseRequestItem;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseRequestItemRepository
{
    public function getAll(int $perPage = 15, ?int $purchaseReqId = null, ?int $branchId = null): LengthAwarePaginator
    {
        return PurchaseRequestItem::with('material')
            ->when($purchaseReqId, fn ($query) => $query->where('purchase_req_id', $purchaseReqId))
            ->when(
                $branchId !== null,
                fn ($query) => $query->whereHas('request', fn ($q) => $q->where('branch_id', $branchId))
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function findById(PurchaseRequestItem $purchaseRequestItem): PurchaseRequestItem
    {
        return $purchaseRequestItem->load(['material', 'request']);
    }

    public function create(PurchaseRequestItemDTO $dto): PurchaseRequestItem
    {
        return PurchaseRequestItem::create($dto->toArray());
    }

    public function update(PurchaseRequestItem $purchaseRequestItem, PurchaseRequestItemDTO $dto): PurchaseRequestItem
    {
        $purchaseRequestItem->update($dto->toArray());
        return $purchaseRequestItem->fresh('material');
    }

    public function delete(PurchaseRequestItem $purchaseRequestItem): bool
    {
        return $purchaseRequestItem->delete();
    }
}