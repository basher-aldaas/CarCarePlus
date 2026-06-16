<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'phone',
        'address',
        'city',
        'latitude',
        'longitude',
        'status',
        'rating_avg',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating_avg' => 'decimal:2',
    ];

}
