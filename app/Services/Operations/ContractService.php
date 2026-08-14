<?php

namespace App\Services\Operations;

use App\DTOs\ContractDTO;
use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Repositories\Eloquent\ContractRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function __construct(protected ContractRepository $contractRepository)
    {}

    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->contractRepository->getAll($perPage);
    }

    public function show(Contract $contract): Contract
    {
        return $this->contractRepository->findById($contract);
    }

    public function store(ContractDTO $dto, int $createdBy): Contract
    {
        return DB::transaction(function () use ($dto, $createdBy) {
            $dto->created_by = $createdBy;
            $dto->status ??= ContractStatus::DRAFT->value;

            return $this->contractRepository->create($dto);
        });
    }

    public function update(Contract $contract, ContractDTO $dto): Contract
    {
        return DB::transaction(function () use ($contract, $dto) {
            return $this->contractRepository->update($contract, $dto);
        });
    }

    public function delete(Contract $contract): bool
    {
        return DB::transaction(function () use ($contract) {
            return $this->contractRepository->delete($contract);
        });
    }
}