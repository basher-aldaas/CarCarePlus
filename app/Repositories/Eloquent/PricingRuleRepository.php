<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PricingRuleDTO;
use App\Models\PricingRule;
use Illuminate\Database\Eloquent\Collection;

class PricingRuleRepository
{
    public function getAll(): Collection
    {
        return PricingRule::with('ruleType')->get();
    }

    public function findById(PricingRule $pricingRule): PricingRule
    {
        return $pricingRule->load('ruleType');
    }

    public function create(PricingRuleDTO $dto): PricingRule
    {
        return PricingRule::create($dto->toArray());
    }

    public function update(PricingRule $pricingRule, PricingRuleDTO $dto): PricingRule
    {
        $pricingRule->update($dto->toArray());
        return $pricingRule->fresh('ruleType');
    }

    public function delete(PricingRule $pricingRule): bool
    {
        return $pricingRule->delete();
    }
}
