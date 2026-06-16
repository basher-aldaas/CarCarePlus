<?php

namespace App\Models;

use App\Enums\UserPackageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPackage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'package_id',
        'start_date',
        'end_date',
        'remaining_count',
        'status',
    ];

    protected $casts = [
        'status' => UserPackageStatus::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package() : BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

}
