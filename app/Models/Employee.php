<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'type',
        'is_active',
        'rating_avg',
    ];

    protected $casts = [
        'type' => 'boolean',
        'is_active' => 'boolean',
        'rating_avg' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function orders(): HasMany
    { return $this->hasMany(Order::class, 'employee_id');
    }
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'employee_id');
    }
    public function reports(): HasMany
    {
        return $this->hasMany(EmployeeReport::class, 'employee_id');
    }
    public function sparePartRequests(): HasMany
    {
        return $this->hasMany(SparePartRequest::class, 'employee_id');
    }
    public function gpsLogs(): HasMany
    {
        return $this->hasMany(GpsLog::class, 'employee_id');
    }
}
