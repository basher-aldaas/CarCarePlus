<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PricingRuleTypeDTO;
use App\Models\PricingRuleType;
use Illuminate\Pagination\LengthAwarePaginator;

class PricingRuleTypeRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return PricingRuleType::paginate($perPage);
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
