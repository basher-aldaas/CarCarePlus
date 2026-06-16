<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    public $timestamps = false;

    protected $table = 'inventory';

    protected $fillable = [
        'branch_id',
        'material_id',
        'quantity',
        'min_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'min_quantity' => 'decimal:2',
    ];

    public function branch() : BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function material() : BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
