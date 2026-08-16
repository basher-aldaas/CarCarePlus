<?php

namespace App\Services\Operations;

use App\Enums\WalletTransactionEnums\WalletTransactionReason;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\Operations\WalletAdjustedAdminNotification;
use App\Notifications\Operations\WalletAdjustedNotification;
use App\Repositories\Eloquent\WalletRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

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

        $wallet = $this->forUser($userId);
        $admin = auth()->user();

        // Notify the customer whose wallet changed.
        $wallet->user?->notify(new WalletAdjustedNotification($wallet, $amount, $note));

        // Notify the super admin(s) who made / oversee the change, recording
        // which admin adjusted which customer. Skip the acting admin so they
        // are not notified about their own action.
        $superAdmins = User::role('super_admin')
            ->when($admin !== null, fn ($q) => $q->where('id', '!=', $admin->id))
            ->get();

        if ($superAdmins->isNotEmpty()) {
            Notification::send(
                $superAdmins,
                new WalletAdjustedAdminNotification($wallet, $admin, $amount, $note),
            );
        }

        return $wallet;
    }
}