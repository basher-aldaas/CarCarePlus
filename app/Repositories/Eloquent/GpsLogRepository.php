<?php

namespace App\Repositories\Eloquent;

use App\DTOs\GpsLogDTO;
use App\Models\GpsLog;
use Illuminate\Pagination\LengthAwarePaginator;

class GpsLogRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return GpsLog::with(['employee.user', 'order'])
            ->when($filters['employee_id'] ?? null, fn ($query, $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['order_id'] ?? null, fn ($query, $orderId) => $query->where('order_id', $orderId))
            ->latest('recorded_at')
            ->paginate($perPage);
    }

    public function findById(GpsLog $gpsLog): GpsLog
    {
        return $gpsLog->load(['employee.user', 'order']);
    }

    public function create(GpsLogDTO $dto): GpsLog
    {
        return GpsLog::create($dto->toArray());
    }
}