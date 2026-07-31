<?php

namespace App\Repositories\Eloquent;

use App\DTOs\AiRuleDTO;
use App\Models\AiRule;
use Illuminate\Database\Eloquent\Collection;

class AiRuleRepository
{
    public function getAll(): Collection
    {
        return AiRule::get();
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
}
