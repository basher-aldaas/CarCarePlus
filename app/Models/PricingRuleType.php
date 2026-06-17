<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingRuleType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'name_ar',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(PricingRule::class, 'pricing_rule_type_id');
    }
}
