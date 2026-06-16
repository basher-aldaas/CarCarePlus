<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointsConfig extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'earn_per_amount',
        'redeem_value',
        'min_redeem',
        'expiry_days',
        'max_earn_per_order',
        'is_active',
    ];

    protected $casts = [
        'earn_per_amount' => 'decimal:2',
        'redeem_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

}
