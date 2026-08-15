<?php

namespace App\Repositories\Eloquent;

use App\Models\PurchasePayment;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchasePaymentRepository
{
    public function getAll(int $perPage = 15, ?int $branchId = null): LengthAwarePaginator
    {
        return PurchasePayment::with(['branch', 'payer', 'purchaseRequest'])
            ->when(
                $branchId !== null,
                fn ($query) => $query->where('branch_id', $branchId)
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function findById(PurchasePayment $purchasePayment): PurchasePayment
    {
        return $purchasePayment->load([
            'branch',
            'payer',
            'purchaseRequest.items.material',
        ]);
    }
}