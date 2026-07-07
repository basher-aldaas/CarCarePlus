<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\AuhDTOs\RegisterCompanyCustomerDTO;
use App\DTOs\AuhDTOs\RegisterWorkshopDTO;
use App\DTOs\CustomersDTOs\RegisterCustomerDTO;
use App\DTOs\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\RegistersRequest\RegisterCompanyCustomerRequest;
use App\Http\Requests\AuthRequests\RegistersRequest\RegisterCustomerRequest;
use App\Http\Requests\AuthRequests\RegistersRequest\RegisterWorkshopRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\Response;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(protected RegistrationService $registrationService) {}

    /**
     * Type 1: Personal customer — self register, active immediately + token.
     */
    public function customer(RegisterCustomerRequest $request): JsonResponse
    {
        $dto = UserDTO::fromArray($request->validated());
        $result = $this->registrationService->registerCustomer($dto);

        $userData = (new UserResource($result['user']))
            ->additional(['token' => $result['token']]);

        return Response::Success(
            data: $userData,
            message: __('Account registered successfully'),
            code: 201
        );
    }

    /**
     * Type 2a: Company customer — submit a registration request (pending approval).
     */
    public function company(RegisterCompanyCustomerRequest $request): JsonResponse
    {
        $dto = RegisterCompanyCustomerDTO::fromArray($request->validated());
        $result = $this->registrationService->registerCompany($dto);

        return Response::Success(
            data: new UserResource($result['user']),
            message: __('Registration request submitted and is pending approval'),
            code: 201
        );
    }

    /**
     * Type 2b: Workshop — submit a registration request (pending approval).
     */
    public function workshop(RegisterWorkshopRequest $request): JsonResponse
    {
        $dto = RegisterWorkshopDTO::fromArray($request->validated());
        $result = $this->registrationService->registerWorkshop($dto);

        return Response::Success(
            data: new UserResource($result['user']),
            message: __('Registration request submitted and is pending approval'),
            code: 201
        );
    }
}
