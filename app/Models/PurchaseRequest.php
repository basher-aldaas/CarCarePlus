<?php

namespace App\Models;

use App\Enums\PurchaseRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'branch_id',
        'from_branch_id',
        'status',
        'total_amount',
        'notes',
        'request_type',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'status' => PurchaseRequestStatus::class,
        'request_type' => 'boolean',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function branch() : BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function fromBranch() : BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function items() : HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}
