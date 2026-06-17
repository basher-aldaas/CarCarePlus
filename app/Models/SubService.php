<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubService extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'name',
        'name_ar',
        'description',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function orderSubServices(): HasMany
    {
        return $this->hasMany(OrderSubService::class, 'sub_service_id');
    }
    public function packageOverrides(): HasMany
    {
        return $this->hasMany(PackageServiceSubService::class, 'sub_service_id');
    }

}
