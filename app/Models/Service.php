<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'service_type_id',
        'name',
        'name_ar',
        'description',
        'base_price',
        'is_vip_available',
        'vip_extra_price',
        'duration_minutes',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'vip_extra_price' => 'decimal:2',
        'is_vip_available' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
    public function subServices(): HasMany
    {
        return $this->hasMany(SubService::class, 'service_id');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'service_id');
    }
    public function packageServices(): HasMany
    {
        return $this->hasMany(PackageService::class, 'service_id');
    }

}
