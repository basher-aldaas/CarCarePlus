<?php

namespace App\Models;

use App\Enums\SystemSettingType;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => SystemSettingType::class,
    ];
}
