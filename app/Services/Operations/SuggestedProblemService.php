<?php

namespace App\Services\Operations;

use App\DTOs\SuggestedProblemDTO;
use App\Models\SuggestedProblem;
use App\Repositories\Eloquent\SuggestedProblemRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SuggestedProblemService
{
    public function __construct(protected SuggestedProblemRepository $suggestedProblemRepository)
    {}
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->suggestedProblemRepository->getAll($perPage);
    }

    public function show(SuggestedProblem $suggestedProblem): SuggestedProblem
    {
        return $this->suggestedProblemRepository->findById($suggestedProblem);
    }

    public function store(SuggestedProblemDTO $dto): SuggestedProblem
    {
        return DB::transaction(function () use($dto){
            return $this->suggestedProblemRepository->create($dto);
        });
    }

    public function update(SuggestedProblem $suggestedProblem, SuggestedProblemDTO $dto): SuggestedProblem
    {
        return DB::transaction(function () use ($suggestedProblem, $dto){
            return $this->suggestedProblemRepository->update($suggestedProblem, $dto);
        });
    }

    public function delete(SuggestedProblem $suggestedProblem): bool
    {
        return DB::transaction(function () use($suggestedProblem){
            return $this->suggestedProblemRepository->delete($suggestedProblem);
        });
    }
}
