<?php

namespace App\Models;

use App\Enums\CarEnums\CarTypeSize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TowingDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'car_type_size',
        'destination_lat',
        'destination_lng',
        'destination_address',
        'notes',
    ];

    protected $casts = [
        'car_type_size' => CarTypeSize::class,
        'destination_lat' => 'decimal:7',
        'destination_lng' => 'decimal:7',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}