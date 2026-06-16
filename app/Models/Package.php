<?php

namespace App\Models;

use App\Enums\PackageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
        'discount_pct',
        'services_count',
        'valid_days',
        'is_active',
    ];

    protected $casts = [
        'type' => PackageType::class,
        'price' => 'decimal:2',
        'discount_pct' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function services() : HasMany
    {
        return $this->hasMany(PackageService::class);
    }

}
