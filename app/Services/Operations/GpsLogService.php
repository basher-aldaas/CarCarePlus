<?php

namespace App\Services\Operations;

use App\DTOs\GpsLogDTO;
use App\Models\GpsLog;
use App\Repositories\Eloquent\GpsLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GpsLogService
{
    public function __construct(protected GpsLogRepository $gpsLogRepository)
    {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->gpsLogRepository->getAll($perPage);
    }

    public function show(GpsLog $gpsLog): GpsLog
    {
        return $this->gpsLogRepository->findById($gpsLog);
    }

    public function store(GpsLogDTO $dto, int $employeeId): GpsLog
    {
        return DB::transaction(function () use ($dto, $employeeId) {
            $dto->employee_id = $employeeId;
            $dto->recorded_at ??= now()->toDateTimeString();

            return $this->gpsLogRepository->create($dto);
        });
    }
}
