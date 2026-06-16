<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'order_id',
        'latitude',
        'longitude',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'recorded_at' => 'datetime',
    ];

    public function employee() : BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
