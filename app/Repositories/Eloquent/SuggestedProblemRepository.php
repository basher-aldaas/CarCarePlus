<?php

namespace App\Repositories\Eloquent;

use App\DTOs\SuggestedProblemDTO;
use App\Models\SuggestedProblem;
use Illuminate\Pagination\LengthAwarePaginator;

class SuggestedProblemRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return SuggestedProblem::paginate($perPage);
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
