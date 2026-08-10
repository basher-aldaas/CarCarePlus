<?php

namespace App\Models;

use App\Enums\OrderEnums\OrderSubServiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSubService extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'sub_service_id',
        'price',
        'covered_by_package',
        'status',
        'notes',
        'checked_at',
    ];

    protected $casts = [
        'status' => OrderSubServiceStatus::class,
        'price' => 'decimal:2',
        'covered_by_package' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function subService(): BelongsTo
    {
        return $this->belongsTo(SubService::class, 'sub_service_id');
    }

}
