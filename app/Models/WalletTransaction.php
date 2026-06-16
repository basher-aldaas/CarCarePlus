<?php

namespace App\Models;

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
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet() : BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
