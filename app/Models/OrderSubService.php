<?php

namespace App\Models;

use App\Enums\OrderEnums\OrderMaterialStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSubService extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'sub_service_id',
        'price',
        'status',
        'notes',
        'checked_at',
    ];

    protected $casts = [
        'status' => OrderMaterialStatus::class,
        'price' => 'decimal:2',
        'checked_at' => 'datetime',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function subService() : BelongsTo
    {
        return $this->belongsTo(SubService::class);
    }

}
