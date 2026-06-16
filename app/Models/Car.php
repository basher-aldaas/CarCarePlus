<?php

namespace App\Models;

use App\Enums\CarEnums\FuelType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Car extends Model
{
    protected $fillable = [
        'customer_id',
        'company_id',
        'car_type_id',
        'plate_number',
        'brand',
        'model',
        'year',
        'color',
        'fuel_type',
        'cylinders',
        'mileage',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'fuel_type' => FuelType::class,
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function carType(): BelongsTo
    {
        return $this->belongsTo(CarType::class);
    }
}
