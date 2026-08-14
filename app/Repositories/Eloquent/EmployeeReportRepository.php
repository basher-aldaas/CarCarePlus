<?php

namespace App\Repositories\Eloquent;

use App\DTOs\EmployeeReportDTO;
use App\Models\EmployeeReport;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeReportRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return EmployeeReport::with(['order', 'employee.user'])
            ->when($filters['order_id'] ?? null, fn ($query, $orderId) => $query->where('order_id', $orderId))
            ->when($filters['employee_id'] ?? null, fn ($query, $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findById(EmployeeReport $employeeReport): EmployeeReport
    {
        return $employeeReport->load(['order', 'employee.user']);
    }

    public function create(EmployeeReportDTO $dto): EmployeeReport
    {
        return EmployeeReport::create($dto->toArray());
    }

    public function update(EmployeeReport $employeeReport, EmployeeReportDTO $dto): EmployeeReport
    {
        $employeeReport->update($dto->toArray());
        return $employeeReport->fresh(['order', 'employee.user']);
    }

    public function delete(EmployeeReport $employeeReport): bool
    {
        return $employeeReport->delete();
    }
}