<?php

namespace App\Models;

use App\Enums\OtpEnums\OtpChannel;
use App\Enums\OtpEnums\OtpType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'type',
        'channel',
        'is_used',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'type' => OtpType::class,
        'channel' => OtpChannel::class,
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
