<?php

namespace App\Models;

use App\Enums\CarEnums\FuelType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_type_id',
        'brand_id',
        'branch_id',
        'plate_number',
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function carType(): BelongsTo
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'car_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(CarBrand::class, 'brand_id');
    }
}
