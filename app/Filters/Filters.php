<?php


namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class Filters
{
    protected Builder $builder;

    protected array $filters = [];

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach (request()->only($this->filters) as $filter => $value) {

            if ($value === null || $value === '') {
                continue;
            }

            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }
}
