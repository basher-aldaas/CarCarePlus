<?php

namespace App\Models;

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
}
