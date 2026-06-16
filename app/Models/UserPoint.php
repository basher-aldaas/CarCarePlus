<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPoint extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    public function customer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
