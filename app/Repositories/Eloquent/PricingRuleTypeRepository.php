<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PricingRuleTypeDTO;
use App\Models\PricingRuleType;
use Illuminate\Database\Eloquent\Collection;

class PricingRuleTypeRepository
{
    public function getAll(): Collection
    {
        return PricingRuleType::get();
    }

    public function findById(PricingRuleType $pricingRuleType): PricingRuleType
    {
        return $pricingRuleType;
    }

    public function create(PricingRuleTypeDTO $dto): PricingRuleType
    {
         return PricingRuleType::create($dto->toArray());
    }

    public function update(PricingRuleType $pricingRuleType, PricingRuleTypeDTO $dto): PricingRuleType
    {
        $pricingRuleType->update($dto->toArray());
        return $pricingRuleType->fresh();
    }

    public function delete(PricingRuleType $pricingRuleType): bool
    {
        return $pricingRuleType->delete();
    }
}
