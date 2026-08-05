<?php

namespace App\Repositories\Eloquent;

use App\Enums\WalletTransactionEnums\WalletTransactionReason;
use App\Enums\WalletTransactionEnums\WalletTransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Validation\ValidationException;

class WalletRepository
{
    /**
     * Debit a user's wallet, logging the transaction. Locks the wallet row
     * for the duration of the check-and-update to stay safe under
     * concurrent debits.
     */
    public function debit(
        int $userId,
        float $amount,
        WalletTransactionReason $reason,
        ?string $note = null,
    ): WalletTransaction {
        $wallet = Wallet::lockForUpdate()->firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );

        $balanceBefore = $wallet->balance;

        if ($balanceBefore < $amount) {
            throw ValidationException::withMessages([
                'wallet' => [__('Insufficient wallet balance')],
            ]);
        }

        $balanceAfter = $balanceBefore - $amount;

        $wallet->update(['balance' => $balanceAfter]);

        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $userId,
            'type' => WalletTransactionType::DEBIT,
            'reason' => $reason,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'note' => $note,
        ]);
    }
}
