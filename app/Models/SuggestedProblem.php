<?php

namespace App\Models;

use App\Enums\SuggestedProblemCategory;
use Illuminate\Database\Eloquent\Model;

class SuggestedProblem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'category',
    ];

    protected $casts = [
        'category' => SuggestedProblemCategory::class,
    ];
}
