<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartRequest extends Model
{
    protected $fillable = [
        'order_id',
        'employee_id',
        'admin_id',
        'part_name',
        'part_number',
        'specifications',
        'status',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function employee() : BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function admin() : BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

}
