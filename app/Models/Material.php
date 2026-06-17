<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'material_unit_id',
        'name',
        'name_ar',
        'description',
        'unit_price',
        'is_vip_material',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_vip_material' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MaterialUnit::class, 'material_unit_id');
    }
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'material_id');
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'material_id');
    }
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class, 'material_id');
    }
    public function orderMaterials(): HasMany
    {
        return $this->hasMany(OrderMaterial::class, 'material_id');
    }

}
