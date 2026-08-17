<?php

namespace App\Filters;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogFilter extends Filters
{
    protected array $filters = [
        'user_id',
        'table_name',
        'action',
        'from_date',
        'to_date',
        'search',
    ];

    public function user_id($value): Builder
    {
        return $this->builder->where('user_id', $value);
    }

    public function table_name($value): Builder
    {
        return $this->builder->where('table_name', $value);
    }

    public function action($value): Builder
    {
        return $this->builder->where('action', $value);
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
            $query->where('table_name', 'like', "%{$value}%")
                ->orWhere('action', 'like', "%{$value}%")
                ->orWhere('ip_address', 'like', "%{$value}%");
        });
    }
    public function getAll(int $perPage = 15, array $filters = [])
    {
        return (new AuditLogFilter())
            ->apply(
                AuditLog::with('user')
            )
            ->latest('id')
            ->paginate($perPage);
    }
}
