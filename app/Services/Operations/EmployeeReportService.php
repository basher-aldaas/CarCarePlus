<?php

namespace App\Services\Operations;

use App\DTOs\EmployeeReportDTO;
use App\Enums\EmployeeReportStatus;
use App\Models\EmployeeReport;
use App\Repositories\Eloquent\EmployeeReportRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeReportService
{
    public function __construct(protected EmployeeReportRepository $employeeReportRepository)
    {}

    /**
     * @param  array<string, mixed>  $filters
     */

    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->employeeReportRepository->getAll($perPage);
    }

    public function show(EmployeeReport $employeeReport): EmployeeReport
    {
        return $this->employeeReportRepository->findById($employeeReport);
    }

    public function store(EmployeeReportDTO $dto, int $employeeId): EmployeeReport
    {
        return DB::transaction(function () use ($dto, $employeeId) {
            $dto->employee_id = $employeeId;
            $dto->status ??= EmployeeReportStatus::PENDING->value;

            return $this->employeeReportRepository->create($dto);
        });
    }
}
