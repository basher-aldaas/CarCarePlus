<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulingConflict extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id_1',
        'order_id_2',
        'conflict_time',
        'resolution_type',
        'discount_offered',
        'extra_cost',
        'resolved_at',
    ];

    protected $casts = [
        'conflict_time' => 'datetime',
        'resolved_at' => 'datetime',
        'discount_offered' => 'decimal:2',
        'extra_cost' => 'decimal:2',
    ];

    public function order1() : BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id_1');
    }

    public function order2() : BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id_2');
    }

}
