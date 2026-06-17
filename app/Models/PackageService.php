<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageService extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'package_id',
        'service_id',
        'allowed_count',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function subServiceOverrides(): HasMany
    {
        return $this->hasMany(PackageServiceSubService::class, 'package_service_id');
    }
}
