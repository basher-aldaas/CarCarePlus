<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pricing_rule_type_id' => $this->pricing_rule_type_id,
            'rule_type' => new PricingRuleTypeResource($this->whenLoaded('ruleType')),
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'value' => $this->value,
            'conditions' => $this->conditions,
            'is_active' => $this->is_active,
        ];
    }
}
