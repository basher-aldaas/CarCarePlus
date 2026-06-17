<?php

namespace App\Models;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'package_id',
        'cash_confirmed_by',
        'payment_number',
        'type',
        'method',
        'status',
        'amount',
        'points_used',
    ];

    protected $casts = [
        'type' => PaymentType::class,
        'method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function package(): BelongsTo
    {
        return $this->belongsTo(UserPackage::class, 'package_id');
    }
    public function cashConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cash_confirmed_by');
    }
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }
}
