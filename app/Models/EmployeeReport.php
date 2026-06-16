<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeReport extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'employee_id',
        'problem_description',
        'affected_parts',
        'images',
        'recommendation',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => EmployeeReport::class,
        'affected_parts' => 'array',
        'images' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function employee() : BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
