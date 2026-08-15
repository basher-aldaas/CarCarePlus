<?php

namespace App\Models;

use App\Enums\SparePartRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartRequest extends Model
{
    protected $fillable = [
        'order_id',
        'employee_id',
        'material_id',
        'quantity',
        'specifications',
        'status',
        'notes',
        'decided_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'status' => SparePartRequestStatus::class,
        'decided_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
