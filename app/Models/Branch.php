<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'admin_id',
        'name',
        'name_ar',
        'city',
        'address',
        'latitude',
        'longitude',
        'phone',
        'is_active',
        'working_hours',
        'is_24h',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_24h' => 'boolean',
        'working_hours' => 'array',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'branch_id');
    }
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'branch_id');
    }
    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'branch_id');
    }
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'from_branch_id');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'branch_id');
    }
}
