<?php


namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends Filters
{
    protected array $filters = [
        'customer_id',
        'company_id',
        'car_id',
        'branch_id',
        'workshop_id',
        'employee_id',
        'service_id',
        'category_id',
        'status',
        'booking_type',
        'is_vip',
        'from_date',
        'to_date',
        'search',
    ];

    public function customer_id($value): Builder
    {
        return $this->builder->where('customer_id', $value);
    }

    public function company_id($value): Builder
    {
        return $this->builder->where('company_id', $value);
    }

    public function car_id($value): Builder
    {
        return $this->builder->where('car_id', $value);
    }

    public function branch_id($value): Builder
    {
        return $this->builder->where('branch_id', $value);
    }

    public function workshop_id($value): Builder
    {
        return $this->builder->where('workshop_id', $value);
    }

    public function employee_id($value): Builder
    {
        return $this->builder->where('employee_id', $value);
    }

    public function service_id($value): Builder
    {
        return $this->builder->where('service_id', $value);
    }

    public function category_id($value): Builder
    {
        return $this->builder->where('category_id', $value);
    }

    public function status($value): Builder
    {
        return $this->builder->where('status', $value);
    }

    public function booking_type($value): Builder
    {
        return $this->builder->where('booking_type', $value);
    }

    public function is_vip($value): Builder
    {
        return $this->builder->where('is_vip', filter_var($value, FILTER_VALIDATE_BOOLEAN));
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
        return $this->builder->where(function ($query) use ($value) {
            $query->where('location_address', 'like', "%{$value}%")
                ->orWhere('notes', 'like', "%{$value}%");
        });
    }
}
