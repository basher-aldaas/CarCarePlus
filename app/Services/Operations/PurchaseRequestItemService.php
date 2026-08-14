<?php

namespace App\Services\Operations;

use App\Exceptions\PurchaseRequestShowUnauthorizedException;
use App\Models\Branch;
use App\Models\PurchaseRequestItem;
use App\Repositories\Eloquent\PurchaseRequestItemRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseRequestItemService
{
    public function __construct(protected PurchaseRequestItemRepository $purchaseRequestItemRepository)
    {}

    public function index(int $perPage = 15, ?int $purchaseReqId = null): LengthAwarePaginator
    {
        $user = auth()->user();

        // Super admin sees every branch's items; an admin is limited to the
        // items of purchase requests belonging to the branch he manages.
        $branchId = $user->hasRole('super_admin')
            ? null
            : Branch::where('admin_id', $user->id)->value('id');

        return $this->purchaseRequestItemRepository->getAll($perPage, $purchaseReqId, $branchId);
    }

    public function show(PurchaseRequestItem $purchaseRequestItem): PurchaseRequestItem
    {
        $user = auth()->user();

        // Super admin sees any item; an admin is limited to items of purchase
        // requests belonging to the branch he manages.
        if ($user->hasRole('super_admin')) {
            return $this->purchaseRequestItemRepository->findById($purchaseRequestItem);
        }

        if ($user->hasRole('admin')) {
            $branchId = Branch::where('admin_id', $user->id)->value('id');

            $purchaseRequestItem->loadMissing('request');

            if ($branchId === null || $branchId !== $purchaseRequestItem->request?->branch_id) {
                throw new PurchaseRequestShowUnauthorizedException();
            }

            return $this->purchaseRequestItemRepository->findById($purchaseRequestItem);
        }

        throw new PurchaseRequestShowUnauthorizedException();
    }
}