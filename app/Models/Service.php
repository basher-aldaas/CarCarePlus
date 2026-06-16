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

    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function subServices() : HasMany
    {
        return $this->hasMany(SubService::class);
    }

}
