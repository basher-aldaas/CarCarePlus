<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PricingRuleDTO;
use App\Models\PricingRule;
use Illuminate\Pagination\LengthAwarePaginator;

class PricingRuleRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return PricingRule::with('ruleType')->paginate($perPage);
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

    /**
     * The single active rule for a given rule type name (e.g. "distance",
     * "off_hours"), used by the pricing engine to price a booking.
     */
    public function findActiveByType(string $typeName): ?PricingRule
    {
        return PricingRule::query()
            ->whereHas('ruleType', fn ($query) => $query->where('name', $typeName))
            ->where('is_active', true)
            ->first();
    }

    /**
     * Some rule types (e.g. "customer_type", "service") have multiple
     * active rows distinguished by a condition value rather than one rule
     * per type — this narrows by that condition too.
     */
    public function findActiveByTypeAndCondition(string $typeName, string $conditionKey, string $conditionValue): ?PricingRule
    {
        return PricingRule::query()
            ->whereHas('ruleType', fn ($query) => $query->where('name', $typeName))
            ->where('is_active', true)
            ->where("conditions->{$conditionKey}", $conditionValue)
            ->first();
    }
}
