<?php

namespace App\Services\Operations;

use App\Models\WalletTransaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletTransactionService
{
    /**
     * Wallet transactions scoped to the caller. Super admin/admin may target
     * any user via $customerId; everyone else is forced to their own.
     */
    public function index(?int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        $user = auth()->user();

        $targetId = $user->hasAnyRole(['super_admin', 'admin'])
            ? ($customerId ?? $user->id)
            : $user->id;

        return WalletTransaction::where('user_id', $targetId)
            ->latest('id')
            ->paginate($perPage);
    }

    public function show(int $id): WalletTransaction
    {
        $transaction = WalletTransaction::findOrFail($id);
        $user = auth()->user();

        if ($transaction->user_id !== $user->id && ! $user->hasAnyRole(['super_admin', 'admin'])) {
            throw new AuthorizationException(__('Unauthorized'));
        }

        return $transaction;
    }
}