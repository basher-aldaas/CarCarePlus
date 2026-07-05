<?php

namespace App\Models;

use App\Enums\WorkshopStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'name_ar',
        'address',
        'city',
        'latitude',
        'longitude',
        'status',
        'rating_avg',
    ];

    protected $casts = [
        'status' => WorkshopStatus::class,
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating_avg' => 'decimal:2',
    ];


    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'workshop_id');
    }

}
