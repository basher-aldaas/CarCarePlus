<?php

namespace App\Services\Operations;

use App\DTOs\PricingRuleDTO;
use App\Models\PricingRule;
use App\Repositories\Eloquent\PricingRuleRepository;
use Illuminate\Support\Facades\DB;

class PricingRuleService
{
    public function __construct(protected PricingRuleRepository $pricingRuleRepository)
    {}
    public function index()
    {
        return $this->pricingRuleRepository->getAll();
    }

    public function show(PricingRule $pricingRule): PricingRule
    {
        return $this->pricingRuleRepository->findById($pricingRule);
    }

    public function store(PricingRuleDTO $dto): PricingRule
    {
        return DB::transaction(function () use($dto){
            return $this->pricingRuleRepository->create($dto);
        });
    }

    public function update(PricingRule $pricingRule, PricingRuleDTO $dto): PricingRule
    {
        return DB::transaction(function () use ($pricingRule, $dto){
            return $this->pricingRuleRepository->update($pricingRule, $dto);
        });
    }

    public function delete(PricingRule $pricingRule): bool
    {
        return DB::transaction(function () use($pricingRule){
            return $this->pricingRuleRepository->delete($pricingRule);
        });
    }
}
