<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarBrand extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'brand_id');
    }

    public function aiRules(): HasMany
    {
        return $this->hasMany(AiRule::class, 'brand_id');
    }
}
