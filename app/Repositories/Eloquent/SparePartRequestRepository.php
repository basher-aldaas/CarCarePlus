<?php

namespace App\Repositories\Eloquent;

use App\DTOs\SparePartRequestDTO;
use App\Models\SparePartRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class SparePartRequestRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return SparePartRequest::with(['order', 'employee.user', 'admin'])
            ->when($filters['order_id'] ?? null, fn ($query, $orderId) => $query->where('order_id', $orderId))
            ->when($filters['employee_id'] ?? null, fn ($query, $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findById(SparePartRequest $sparePartRequest): SparePartRequest
    {
        return $sparePartRequest->load(['order', 'employee.user', 'admin']);
    }

    public function create(SparePartRequestDTO $dto): SparePartRequest
    {
        return SparePartRequest::create($dto->toArray());
    }

    public function update(SparePartRequest $sparePartRequest, SparePartRequestDTO $dto): SparePartRequest
    {
        $sparePartRequest->update($dto->toArray());
        return $sparePartRequest->fresh(['order', 'employee.user', 'admin']);
    }

    public function delete(SparePartRequest $sparePartRequest): bool
    {
        return $sparePartRequest->delete();
    }
}