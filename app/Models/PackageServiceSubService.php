<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageServiceSubService extends Model
{
    protected $fillable = [
        'package_service_id',
        'sub_service_id',
        'price_override',
        'is_active',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function packageService(): BelongsTo
    {
        return $this->belongsTo(PackageService::class, 'package_service_id');
    }
    public function subService(): BelongsTo
    {
        return $this->belongsTo(SubService::class, 'sub_service_id');
    }
}
