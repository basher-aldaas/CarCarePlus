<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $fillable = [
        'super_admin_id',
        'pricing_rule_type_id',
        'name',
        'value',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }
    public function ruleType(): BelongsTo
    {
        return $this->belongsTo(PricingRuleType::class, 'pricing_rule_type_id');
    }
}
