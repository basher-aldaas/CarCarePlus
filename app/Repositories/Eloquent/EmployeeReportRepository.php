<?php

namespace App\Repositories\Eloquent;

use App\DTOs\EmployeeReportDTO;
use App\Filters\EmployeeReportFilter;
use App\Models\EmployeeReport;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeReportRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        $query = EmployeeReport::with(['order', 'employee.user'])
            ->latest('id');
        return $query->paginate($perPage);
    }

    public function findById(EmployeeReport $employeeReport): EmployeeReport
    {
        return $employeeReport->load(['order', 'employee.user']);
    }

    public function create(EmployeeReportDTO $dto): EmployeeReport
    {
        return EmployeeReport::create($dto->toArray());
    }

    public function update(
        EmployeeReport $employeeReport,
        EmployeeReportDTO $dto
    ): EmployeeReport {
        $employeeReport->update($dto->toArray());

        return $employeeReport->fresh(['order', 'employee.user']);
    }

    public function delete(EmployeeReport $employeeReport): bool
    {
        return $employeeReport->delete();
    }
}
