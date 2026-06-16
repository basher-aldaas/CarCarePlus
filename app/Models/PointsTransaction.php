<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'expires_at',
        'note',
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function customer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

}
