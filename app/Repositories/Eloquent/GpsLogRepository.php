<?php

namespace App\Repositories\Eloquent;

use App\DTOs\GpsLogDTO;
use App\Filters\GpsLogFilter;
use App\Models\GpsLog;
use Illuminate\Pagination\LengthAwarePaginator;

class GpsLogRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return (new GpsLogFilter())
            ->apply(
                GpsLog::with(['employee.user', 'order'])
            )
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
