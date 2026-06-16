<?php

namespace App\Models;

use App\Enums\RefundEnums\RefundDestination;
use App\Enums\RefundEnums\RefundReason;
use App\Enums\RefundEnums\RefundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'order_id',
        'user_id',
        'amount',
        'reason',
        'status',
        'destination',
        'notes',
    ];

    protected $casts = [
        'reason' => RefundReason::class,
        'status' => RefundStatus::class,
        'destination' => RefundDestination::class,
        'amount' => 'decimal:2',
    ];

    public function payment() : BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
