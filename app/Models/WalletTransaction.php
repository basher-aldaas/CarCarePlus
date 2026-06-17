<?php

namespace App\Models;

use App\Enums\WalletTransactionEnums\WalletTransactionReason;
use App\Enums\WalletTransactionEnums\WalletTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'reason',
        'amount',
        'balance_before',
        'balance_after',
        'note',
    ];

    protected $casts = [
        'type' => WalletTransactionType::class,
        'reason' => WalletTransactionReason::class,
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
