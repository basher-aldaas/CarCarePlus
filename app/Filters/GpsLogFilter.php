<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class GpsLogFilter extends Filters
{
    protected array $filters = [
        'employee_id',
        'order_id',
        'from_date',
        'to_date',
    ];

    public function employee_id($value): Builder
    {
        return $this->builder->where('employee_id', $value);
    }

    public function order_id($value): Builder
    {
        return $this->builder->where('order_id', $value);
    }

    public function from_date($value): Builder
    {
        return $this->builder->whereDate('recorded_at', '>=', $value);
    }

    public function to_date($value): Builder
    {
        return $this->builder->whereDate('recorded_at', '<=', $value);
    }
}
