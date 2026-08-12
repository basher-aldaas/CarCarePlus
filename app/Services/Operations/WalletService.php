<?php

namespace App\Services\Operations;

use App\Enums\WalletTransactionEnums\WalletTransactionReason;
use App\Models\Wallet;
use App\Repositories\Eloquent\WalletRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function __construct(protected WalletRepository $walletRepository)
    {}

    /**
     * All wallets (super admin / admin overview).
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return Wallet::with('user')->latest()->paginate($perPage);
    }

    /**
     * A single user's wallet, created empty on first access so every user
     * always has one.
     */
    public function forUser(int $userId): Wallet
    {
        return Wallet::with('user')->firstOrCreate(['user_id' => $userId], ['balance' => 0]);
    }

    /**
     * Adjust a user's wallet balance (staff action). A positive amount
     * credits, a negative amount debits — each logged as a transaction.
     */
    public function adjust(int $userId, float $amount, ?string $note = null): Wallet
    {
        DB::transaction(function () use ($userId, $amount, $note) {
            if ($amount >= 0) {
                $this->walletRepository->credit($userId, WalletTransactionReason::ADJUSTMENT, $amount, $note);
            } else {
                $this->walletRepository->debit($userId, WalletTransactionReason::ADJUSTMENT, abs($amount), $note);
            }
        });

        return $this->forUser($userId);
    }
}