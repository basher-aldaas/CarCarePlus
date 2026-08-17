<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Http\Responses\Response;
use App\Models\AuditLog;
use App\Services\Operations\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: AuditLogResource::collection(
                $this->auditLogService->index(
                    $request->integer('per_page', 15)
                )
            ),
            message: 'audit logs fetched successfully'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        return Response::Success(
            new AuditLogResource($this->auditLogService->show($auditLog)),
            'audit log fetched successfully'
        );
    }
}
