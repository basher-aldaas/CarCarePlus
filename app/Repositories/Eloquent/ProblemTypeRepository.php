<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ProblemTypeDTO;
use App\Models\ProblemType;
use Illuminate\Database\Eloquent\Collection;

class ProblemTypeRepository
{
    public function getAll(): Collection
    {
        return ProblemType::get();
    }

    public function findById(ProblemType $problemType): ProblemType
    {
        return $problemType;
    }

    public function create(ProblemTypeDTO $dto): ProblemType
    {
        return ProblemType::create($dto->toArray());
    }

    public function update(ProblemType $problemType, ProblemTypeDTO $dto): ProblemType
    {
        $problemType->update($dto->toArray());
        return $problemType->fresh();
    }

    public function delete(ProblemType $problemType): bool
    {
        return $problemType->delete();
    }
}
