<?php

namespace App\Repositories\Eloquent;

use App\Filters\AuditLogFilter;
use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogRepository
{
    public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return (new AuditLogFilter())
            ->apply(AuditLog::with('user'))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findById(AuditLog $auditLog): AuditLog
    {
        return $auditLog->load('user');
    }
}
