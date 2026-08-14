<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ContractDTO;
use App\Models\Contract;
use Illuminate\Pagination\LengthAwarePaginator;

class ContractRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Contract::with(['company', 'workshop', 'creator'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function findById(Contract $contract): Contract
    {
        return $contract->load(['company', 'workshop', 'creator']);
    }

    public function create(ContractDTO $dto): Contract
    {
        return Contract::create($dto->toArray());
    }

    public function update(Contract $contract, ContractDTO $dto): Contract
    {
        $contract->update($dto->toArray());
        return $contract->fresh(['company', 'workshop', 'creator']);
    }

    public function delete(Contract $contract): bool
    {
        return $contract->delete();
    }
}