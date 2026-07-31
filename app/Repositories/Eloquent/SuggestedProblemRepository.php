<?php

namespace App\Repositories\Eloquent;

use App\DTOs\SuggestedProblemDTO;
use App\Models\SuggestedProblem;
use Illuminate\Database\Eloquent\Collection;

class SuggestedProblemRepository
{
    public function getAll(): Collection
    {
        return SuggestedProblem::get();
    }

    public function findById(SuggestedProblem $suggestedProblem): SuggestedProblem
    {
        return $suggestedProblem;
    }

    public function create(SuggestedProblemDTO $dto): SuggestedProblem
    {
        return SuggestedProblem::create($dto->toArray());
    }

    public function update(SuggestedProblem $suggestedProblem, SuggestedProblemDTO $dto): SuggestedProblem
    {
        $suggestedProblem->update($dto->toArray());
        return $suggestedProblem->fresh();
    }

    public function delete(SuggestedProblem $suggestedProblem): bool
    {
        return $suggestedProblem->delete();
    }
}
