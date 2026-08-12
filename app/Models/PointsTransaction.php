<?php

namespace App\Models;

use App\Enums\PointsTransactionType;
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
        'note',
    ];

    protected $casts = [
        'type' => PointsTransactionType::class,
        'points' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

}
