<?php

namespace App\Services\Operations;

use App\Exceptions\PurchasePaymentShowUnauthorizedException;
use App\Models\Branch;
use App\Models\PurchasePayment;
use App\Repositories\Eloquent\PurchasePaymentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchasePaymentService
{
    public function __construct(
        protected PurchasePaymentRepository $purchasePaymentRepository,
    )
    {}

    /**
     * Super admin sees every purchase payment; an admin only the payments of
     * the branch he manages.
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        $user = auth()->user();

        $branchId = null;

        if (!$user->hasRole('super_admin')) {
            $branchId = Branch::where('admin_id', $user->id)->value('id');
        }

        return $this->purchasePaymentRepository->getAll($perPage, $branchId);
    }

    public function show(PurchasePayment $purchasePayment): PurchasePayment
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return $this->purchasePaymentRepository->findById($purchasePayment);
        }

        if ($user->hasRole('admin')) {
            $branchId = Branch::where('admin_id', $user->id)->value('id');

            if ($branchId === null || $branchId !== $purchasePayment->branch_id) {
                throw new PurchasePaymentShowUnauthorizedException();
            }

            return $this->purchasePaymentRepository->findById($purchasePayment);
        }

        throw new PurchasePaymentShowUnauthorizedException();
    }
}