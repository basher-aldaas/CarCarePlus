<?php

namespace App\Services\Operations;

use App\DTOs\AiRuleDTO;
use App\Models\AiRule;
use App\Repositories\Eloquent\AiRuleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AiRuleService
{
    protected $aiRuleRepository;

    public function __construct(AiRuleRepository $aiRuleRepository)
    {
        $this->aiRuleRepository = $aiRuleRepository;
    }
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



    /**
     * Find an active AI rule matching the customer's problem
     * and vehicle information.
     */
    public function findMatchingRule(
        string $problem,
        ?int $brandId = null,
        ?string $carType = null,
        ?string $fuelType = null
    ): ?AiRule {
        return AiRule::query()
            ->where('is_active', true)
            ->where(function ($query) use ($problem) {
                $query
                    ->where(function ($query) use ($problem) {
                        $query
                            ->whereNotNull('condition_key')
                            ->whereNotNull('condition_value')
                            ->where(function ($query) use ($problem) {
                                $query
                                    ->where('condition_value', 'like', "%{$problem}%")
                                    ->orWhere('name', 'like', "%{$problem}%")
                                    ->orWhere('name_ar', 'like', "%{$problem}%");
                            });
                    });
            })
            ->when($brandId, function ($query) use ($brandId) {
                $query->where(function ($query) use ($brandId) {
                    $query
                        ->whereNull('brand_id')
                        ->orWhere('brand_id', $brandId);
                });
            })
            ->when($carType, function ($query) use ($carType) {
                $query->where(function ($query) use ($carType) {
                    $query
                        ->whereNull('car_type')
                        ->orWhere('car_type', $carType);
                });
            })
            ->when($fuelType, function ($query) use ($fuelType) {
                $query->where(function ($query) use ($fuelType) {
                    $query
                        ->whereNull('fuel_type')
                        ->orWhere('fuel_type', $fuelType);
                });
            })
            ->first();
    }


}


