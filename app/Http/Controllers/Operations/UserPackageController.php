<?php

namespace App\Http\Controllers\Operations;

use App\Constants\HttpStatusConstants;
use App\DTOs\UserPackageDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\UserPackageRequest\CreateUserPackageRequest;
use App\Http\Requests\OperationsRequest\UserPackageRequest\UpdateUserPackageRequest;
use App\Http\Resources\UserPackageResource;
use App\Http\Responses\Response;
use App\Models\UserPackage;
use App\Services\Operations\UserPackageService;
use Illuminate\Http\JsonResponse;

class UserPackageController extends Controller
{

    public function __construct(protected UserPackageService $userPackageService)
    {}

    /**
     * List a customer's package subscriptions.
     * SA/admin may pass a customer_id to view any user's subscriptions; other roles always see their own.
     */
    public function index(?int $customer_id = null): JsonResponse
    {
        if (auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            if ($customer_id === null) {
                return Response::Error(data:null,message: 'customer_id is required');
            }
        } else {
            $customer_id = auth()->id();
        }

        $result = $this->userPackageService->index($customer_id);

        return Response::Success(
            data: UserPackageResource::collection($result),
            message: __('User packages retrieved successfully')
        );
    }

    /**
     * Show a single package subscription.
     * SA/admin may view any subscription; other roles may only view their own.
     */
    public function show(UserPackage $userPackage): JsonResponse
    {
        if (!auth()->user()->hasAnyRole(['super_admin', 'admin']) && $userPackage->user_id !== auth()->id()) {
            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: HttpStatusConstants::HTTP_403_FORBIDDEN
            );
        }

        return Response::Success(
            data: new UserPackageResource($userPackage),
            message: __('User package retrieved successfully')
        );
    }

    public function store(CreateUserPackageRequest $request): JsonResponse
    {
        $dto = UserPackageDTO::fromArray($request->validated());

        $userPackage = $this->userPackageService->store($dto);

        return Response::Success(
            data: new UserPackageResource($userPackage),
            message: __('User package created successfully')
        );
    }

    public function update(UpdateUserPackageRequest $request, UserPackage $userPackage): JsonResponse
    {
        $dto = UserPackageDTO::fromArray($request->validated());

        $userPackage = $this->userPackageService->update($userPackage, $dto);

        return Response::Success(
            data: new UserPackageResource($userPackage),
            message: __('User package updated successfully')
        );
    }

    public function destroy(UserPackage $userPackage): JsonResponse
    {
        $this->userPackageService->destroy($userPackage);

        return Response::Success(
            data: [],
            message: __('User package deleted successfully')
        );
    }
}