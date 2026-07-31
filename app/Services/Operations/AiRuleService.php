<?php

namespace App\Services\Operations;

use App\DTOs\AiRuleDTO;
use App\Models\AiRule;
use App\Repositories\Eloquent\AiRuleRepository;
use Illuminate\Support\Facades\DB;

class AiRuleService
{
    public function __construct(protected AiRuleRepository $aiRuleRepository)
    {}
    public function index()
    {
        return $this->aiRuleRepository->getAll();
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
}
