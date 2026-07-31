<?php

namespace App\Services\Operations;

use App\DTOs\PricingRuleTypeDTO;
use App\Models\PricingRuleType;
use App\Repositories\Eloquent\PricingRuleTypeRepository;
use Illuminate\Support\Facades\DB;

class PricingRuleTypeService
{
    public function __construct(protected PricingRuleTypeRepository $pricingRuleTypeRepository)
    {}
    public function index()
    {
        return $this->pricingRuleTypeRepository->getAll();
    }

    public function show(PricingRuleType $pricingRuleType): PricingRuleType
    {
        return $this->pricingRuleTypeRepository->findById($pricingRuleType);
    }

    public function store(PricingRuleTypeDTO $dto): PricingRuleType
    {
        return DB::transaction(function () use($dto){
            return $this->pricingRuleTypeRepository->create($dto);
        });
    }

    public function update(PricingRuleType $pricingRuleType, PricingRuleTypeDTO $dto): PricingRuleType
    {
        return DB::transaction(function () use ($pricingRuleType, $dto){
            return $this->pricingRuleTypeRepository->update($pricingRuleType, $dto);
        });
    }

    public function delete(PricingRuleType $pricingRuleType): bool
    {
        return DB::transaction(function () use($pricingRuleType){
            return $this->pricingRuleTypeRepository->delete($pricingRuleType);
        });
    }
}
