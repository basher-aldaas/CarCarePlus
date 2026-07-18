<?php

namespace App\Http\Controllers\SuperAdmin\Auth;

use App\DTOs\AuhDTOs\RegisterEmployeeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\RegistersRequest\RegisterEmployeeRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\Response;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;

/**
 * Type 3: Staff accounts created by the super admin — washers, mechanics, and
 * branch admins. The employee type on the request decides which is created.
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
            message: __('Staff account created successfully'),
            code: 201
        );
    }
}
