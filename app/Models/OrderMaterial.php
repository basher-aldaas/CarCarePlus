<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMaterial extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'material_id',
        'requested_by',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function material() : BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function requester() : BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

}
