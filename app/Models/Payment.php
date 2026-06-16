<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'amount' => 'decimal:2',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package() : BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function confirmer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'cash_confirmed_by');
    }

}
