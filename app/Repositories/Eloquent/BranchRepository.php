<?php

namespace App\Repositories\Eloquent;

use App\DTOs\BranchesDTO;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class BranchRepository
{
    public function getAll(): Collection
    {
        return Branch::with('manager')->get();
    }

    public function findById(Branch $branch): Branch
    {
        return $branch->load('manager');
    }

    public function create(BranchesDTO $dto): Branch
    {
        return Branch::create($dto->toArray());
    }

    public function update(Branch $branch, BranchesDTO $dto): Branch
    {
        $branch->update($dto->toArray());
        return $branch->fresh('manager');
    }

    public function delete(Branch $branch): bool
    {
        return $branch->delete();
    }
}
