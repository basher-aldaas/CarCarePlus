<?php

namespace App\Services\Operations;

use App\DTOs\AiRuleDTO;
use App\Models\AiRule;
use App\Repositories\Eloquent\AiRuleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AiRuleService
{
    public function __construct(protected AiRuleRepository $aiRuleRepository)
    {}
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->aiRuleRepository->getAll($perPage);
    }

    public function show(AiRule $aiRule): AiRule
    {
        return $this->aiRuleRepository->findById($aiRule);
    }

    public function store(AiRuleDTO $dto): AiRule
    {
        return DB::transaction(function () use($dto){
            return $this->aiRuleRepository->create($dto);
        });
    }

    public function update(AiRule $aiRule, AiRuleDTO $dto): AiRule
    {
        return DB::transaction(function () use ($aiRule, $dto){
            return $this->aiRuleRepository->update($aiRule, $dto);
        });
    }

    public function delete(AiRule $aiRule): bool
    {
        return DB::transaction(function () use($aiRule){
            return $this->aiRuleRepository->delete($aiRule);
        });
    }
    public function findMatchingRule(
        string $problem,
        ?int $brandId = null,
        ?string $carType = null,
        ?string $fuelType = null
    ): ?AiRule {

        return AiRule::query()

            ->where('is_active', true)

            ->when($brandId, function ($q) use ($brandId) {
                $q->where(function ($query) use ($brandId) {
                    $query->whereNull('brand_id')
                        ->orWhere('brand_id', $brandId);
                });
            })

            ->when($carType, function ($q) use ($carType) {
                $q->where(function ($query) use ($carType) {
                    $query->whereNull('car_type')
                        ->orWhere('car_type', $carType);
                });
            })

            ->when($fuelType, function ($q) use ($fuelType) {
                $q->where(function ($query) use ($fuelType) {
                    $query->whereNull('fuel_type')
                        ->orWhere('fuel_type', $fuelType);
                });
            })

            ->where(function ($query) use ($problem) {

                $query

                    ->where('name', 'like', "%{$problem}%")

                    ->orWhere('name_ar', 'like', "%{$problem}%")

                    ->orWhere('condition_value', 'like', "%{$problem}%");

            })

            ->first();
    }
}
