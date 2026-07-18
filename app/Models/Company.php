<?php

namespace App\Models;

use App\Enums\CompanyStatus;
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
        'status',
        'is_active',
    ];

    protected $casts = [
        'status' => CompanyStatus::class,
        'is_active' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
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
