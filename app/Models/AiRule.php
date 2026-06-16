<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRule extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'type',
        'condition_key',
        'condition_value',
        'car_brand',
        'car_type',
        'fuel_type',
        'response_template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
