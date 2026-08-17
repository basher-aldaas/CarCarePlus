<?php


namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class EmployeeReportFilter extends Filters
{
    protected array $filters = [
        'order_id',
        'employee_id',
        'status',
        'from_date',
        'to_date',
        'search',
    ];

    public function order_id($value): Builder
    {
        return $this->builder->where('order_id', $value);
    }

    public function employee_id($value): Builder
    {
        return $this->builder->where('employee_id', $value);
    }

    public function status($value): Builder
    {
        return $this->builder->where('status', $value);
    }

    public function from_date($value): Builder
    {
        return $this->builder->whereDate('created_at', '>=', $value);
    }

    public function to_date($value): Builder
    {
        return $this->builder->whereDate('created_at', '<=', $value);
    }

    public function search($value): Builder
    {
        return $this->builder->where('problem_description', 'like', "%{$value}%");
    }
}
