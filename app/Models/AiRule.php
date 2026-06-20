<?php

namespace App\Models;

use App\Enums\AiRuleConditionType;
use App\Enums\CarEnums\CarTypeSize;
use App\Enums\CarEnums\FuelType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRule extends Model
{
    protected $fillable = [
        'brand_id',
        'name',
        'name_ar',
        'type',
        'condition_key',
        'condition_value',
        'car_type',
        'fuel_type',
        'response_template',
        'is_active',
    ];

    protected $casts = [
        'type' => AiRuleConditionType::class,
        'car_type' => CarTypeSize::class,
        'fuel_type' => FuelType::class,
        'is_active' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(CarBrand::class, 'brand_id');
    }
}
