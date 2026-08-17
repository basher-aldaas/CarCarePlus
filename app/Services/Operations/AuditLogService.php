<?php

namespace App\Services\Operations;

use App\Models\AuditLog;
use App\Repositories\Eloquent\AuditLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogService
{
    public function __construct(protected AuditLogRepository $auditLogRepository)
    {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->auditLogRepository->getAll($perPage);
    }

    public function show(AuditLog $auditLog): AuditLog
    {
        return $this->auditLogRepository->findById($auditLog);
    }
}
