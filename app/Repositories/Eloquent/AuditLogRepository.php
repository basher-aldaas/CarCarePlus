<?php

namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return AuditLog::with('user')
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['table_name'] ?? null, fn ($query, $table) => $query->where('table_name', $table))
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findById(AuditLog $auditLog): AuditLog
    {
        return $auditLog->load('user');
    }
}