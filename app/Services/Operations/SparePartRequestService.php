<?php

namespace App\Services\Operations;

use App\DTOs\SparePartRequestDTO;
use App\Enums\SparePartRequestStatus;
use App\Models\SparePartRequest;
use App\Repositories\Eloquent\SparePartRequestRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SparePartRequestService
{
    public function __construct(protected SparePartRequestRepository $sparePartRequestRepository)
    {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function index(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->sparePartRequestRepository->getAll($perPage, $filters);
    }

    public function show(SparePartRequest $sparePartRequest): SparePartRequest
    {
        return $this->sparePartRequestRepository->findById($sparePartRequest);
    }

    public function store(SparePartRequestDTO $dto, int $employeeId): SparePartRequest
    {
        return DB::transaction(function () use ($dto, $employeeId) {
            $dto->employee_id = $employeeId;
            $dto->status ??= SparePartRequestStatus::PENDING->value;

            return $this->sparePartRequestRepository->create($dto);
        });
    }

    public function approve(SparePartRequest $sparePartRequest, int $adminId, ?string $notes): SparePartRequest
    {
        return $this->decide($sparePartRequest, SparePartRequestStatus::APPROVED, $adminId, $notes);
    }

    public function reject(SparePartRequest $sparePartRequest, int $adminId, ?string $notes): SparePartRequest
    {
        return $this->decide($sparePartRequest, SparePartRequestStatus::REJECTED, $adminId, $notes);
    }

    private function decide(SparePartRequest $sparePartRequest, SparePartRequestStatus $status, int $adminId, ?string $notes): SparePartRequest
    {
        return DB::transaction(function () use ($sparePartRequest, $status, $adminId, $notes) {
            $dto = SparePartRequestDTO::fromArray([
                'admin_id' => $adminId,
                'status' => $status->value,
                'notes' => $notes,
            ]);

            return $this->sparePartRequestRepository->update($sparePartRequest, $dto);
        });
    }
}