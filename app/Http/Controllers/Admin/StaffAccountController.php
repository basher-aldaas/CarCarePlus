<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\AdminsDTOs\RegisterAdminDTO;
use App\DTOs\EmployeesDTOs\RegisterEmployeeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\RegistersRequest\RegisterAdminRequest;
use App\Http\Requests\AuthRequests\RegistersRequest\RegisterEmployeeRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\Response;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;

/**
 * Type 3: Accounts created by the super admin (employees & branch admins).
 */
class StaffAccountController extends Controller
{
    public function __construct(protected RegistrationService $registrationService) {}

    public function storeEmployee(RegisterEmployeeRequest $request): JsonResponse
    {
        $dto = RegisterEmployeeDTO::fromArray($request->validated());
        $result = $this->registrationService->createEmployee($dto);

        return Response::Success(
            data: new UserResource($result['user']),
            message: __('Employee account created successfully'),
            code: 201
        );
    }

    public function storeAdmin(RegisterAdminRequest $request): JsonResponse
    {
        $dto = RegisterAdminDTO::fromArray($request->validated());
        $result = $this->registrationService->createAdmin($dto);

        return Response::Success(
            data: new UserResource($result['user']),
            message: __('Admin account created successfully'),
            code: 201
        );
    }
}
