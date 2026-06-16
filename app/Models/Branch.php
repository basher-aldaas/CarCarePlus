<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'admin_id',
        'name',
        'name_ar',
        'city',
        'address',
        'latitude',
        'longitude',
        'phone',
        'is_active',
        'working_hours',
        'is_24h',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_24h' => 'boolean',
        'working_hours' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
