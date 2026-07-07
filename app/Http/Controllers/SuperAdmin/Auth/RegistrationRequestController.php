<?php

namespace App\Http\Controllers\SuperAdmin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\WorkshopResource;
use App\Http\Responses\Response;
use App\Models\Company;
use App\Models\Workshop;
use App\Services\Auth\RegistrationApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Super-admin review of pending company & workshop registration requests.
 */
class RegistrationRequestController extends Controller
{
    public function __construct(protected RegistrationApprovalService $approvalService) {}

    public function companies(): JsonResponse
    {
        return Response::Success(
            data: CompanyResource::collection($this->approvalService->pendingCompanies()),
            message: __('Pending company registration requests')
        );
    }

    public function workshops(): JsonResponse
    {
        return Response::Success(
            data: WorkshopResource::collection($this->approvalService->pendingWorkshops()),
            message: __('Pending workshop registration requests')
        );
    }

    public function approveCompany(Company $company): JsonResponse
    {
        $company = $this->approvalService->approveCompany($company);

        return Response::Success(
            data: new CompanyResource($company->load('owner')),
            message: __('Company registration approved')
        );
    }

    public function rejectCompany(Request $request, Company $company): JsonResponse
    {
        $company = $this->approvalService->rejectCompany($company, $request->input('reason'));

        return Response::Success(
            data: new CompanyResource($company->load('owner')),
            message: __('Company registration rejected')
        );
    }

    public function approveWorkshop(Workshop $workshop): JsonResponse
    {
        $workshop = $this->approvalService->approveWorkshop($workshop);

        return Response::Success(
            data: new WorkshopResource($workshop->load('owner')),
            message: __('Workshop registration approved')
        );
    }

    public function rejectWorkshop(Request $request, Workshop $workshop): JsonResponse
    {
        $workshop = $this->approvalService->rejectWorkshop($workshop, $request->input('reason'));

        return Response::Success(
            data: new WorkshopResource($workshop->load('owner')),
            message: __('Workshop registration rejected')
        );
    }
}
