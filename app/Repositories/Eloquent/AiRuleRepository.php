<?php

namespace App\Repositories\Eloquent;

use App\DTOs\AiRuleDTO;
use App\Models\AiRule;
use Illuminate\Pagination\LengthAwarePaginator;

class AiRuleRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return AiRule::paginate($perPage);
    }

    public function findById(AiRule $aiRule): AiRule
    {
        return $aiRule;
    }

    public function create(AiRuleDTO $dto): AiRule
    {
        return AiRule::create($dto->toArray());
    }

    public function update(AiRule $aiRule, AiRuleDTO $dto): AiRule
    {
        $aiRule->update($dto->toArray());
        return $aiRule;
    }

    public function delete(AiRule $aiRule): bool
    {
        return $aiRule->delete();
    }

    public function findMatchingRule(string $problem)
    {
        return AiRule::where('is_active', true)
            ->whereNotNull('condition_value')
            ->where('condition_value', '!=', '')
            ->where(function ($query) use ($problem) {

                $query->whereRaw(
                    '? LIKE CONCAT("%", condition_value, "%")',
                    [$problem]
                );

            })
            ->first();
    }

}
