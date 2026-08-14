<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PurchaseRequestDTO;
use App\Models\PurchaseRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseRequestRepository
{
    public function getAll(int $perPage = 15, ?int $purchaseRequest = null): LengthAwarePaginator
    {
        return PurchaseRequest::with(['branch', 'fromBranch'])
            ->when(
                $purchaseRequest !== null,
                fn ($query) => $query->where('branch_id', $purchaseRequest)
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function findById(PurchaseRequest $purchaseRequest): PurchaseRequest
    {
        return $purchaseRequest->load([
            'branch',
            'fromBranch',
            'items.material',
            'payment',
        ]);
    }

    public function create(PurchaseRequestDTO $dto): PurchaseRequest
    {
        return PurchaseRequest::create($dto->toArray());
    }

    public function update(PurchaseRequest $purchaseRequest, PurchaseRequestDTO $dto): PurchaseRequest
    {
        $purchaseRequest->update($dto->toArray());
        return $purchaseRequest->fresh(['branch', 'fromBranch', 'payment']);
    }

    public function delete(PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->delete();
    }
}
