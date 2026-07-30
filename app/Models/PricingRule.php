<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $fillable = [
        'pricing_rule_type_id',
        'name',
        'name_ar',
        'value',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];


    public function ruleType(): BelongsTo
    {
        return $this->belongsTo(PricingRuleType::class, 'pricing_rule_type_id');
    }
}
