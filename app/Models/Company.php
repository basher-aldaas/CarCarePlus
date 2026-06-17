<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'name_ar',
        'commercial_reg',
        'tax_number',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'company_id');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'company_id');
    }
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'company_id');
    }
}
