<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'reference_type',
        'reference_id',
        'is_read',
        'read_at',
        'sent_via',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_via' => 'array',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
