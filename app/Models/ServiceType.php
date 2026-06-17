<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'name_ar',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_type_id');
    }
}
