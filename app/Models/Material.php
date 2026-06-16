<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function unit() : BelongsTo
    {
        return $this->belongsTo(MaterialUnit::class, 'material_unit_id');
    }

}
