<?php


namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CarFilter extends Filters
{
    protected array $filters = [
        'user_id',
        'brand_id',
        'car_type_id',
        'fuel_type',
        'is_active',
        'year',
        'min_year',
        'max_year',
        'search',
    ];

    public function user_id($value): Builder
    {
        return $this->builder->where('user_id', $value);
    }

    public function brand_id($value): Builder
    {
        return $this->builder->where('brand_id', $value);
    }

    public function car_type_id($value): Builder
    {
        return $this->builder->where('car_type_id', $value);
    }

    public function fuel_type($value): Builder
    {
        return $this->builder->where('fuel_type', $value);
    }

    public function is_active($value): Builder
    {
        return $this->builder->where(
            'is_active',
            filter_var($value, FILTER_VALIDATE_BOOLEAN)
        );
    }

    public function year($value): Builder
    {
        return $this->builder->where('year', $value);
    }

    public function min_year($value): Builder
    {
        return $this->builder->where('year', '>=', $value);
    }

    public function max_year($value): Builder
    {
        return $this->builder->where('year', '<=', $value);
    }

    public function search($value): Builder
    {
        return $this->builder->where(function (Builder $query) use ($value) {
            $query->where('plate_number', 'like', "%{$value}%")
                ->orWhere('model', 'like', "%{$value}%")
                ->orWhere('color', 'like', "%{$value}%");
        });
    }
}
