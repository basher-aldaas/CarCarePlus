<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialUnit extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'is_decimal',
    ];

    protected $casts = [
        'is_decimal' => 'boolean',
    ];

    public function materials() : HasMany
    {
        return $this->hasMany(Material::class);
    }
}
