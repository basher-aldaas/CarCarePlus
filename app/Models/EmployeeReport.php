<?php

namespace App\Models;

use App\Enums\EmployeeReportStatus;
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
        'status' => EmployeeReportStatus::class,
        'affected_parts' => 'array',
        'images' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
