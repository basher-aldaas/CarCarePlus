<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProblemType extends Model
{

    protected $fillable = [
        'name',
        'name_ar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function roadAssistanceDetails() : HasMany
    {
        return $this->hasMany(RoadAssistanceDetail::class);
    }
}
