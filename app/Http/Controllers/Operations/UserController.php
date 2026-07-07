<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\OperationsDTO\EditProfileDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\EditProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\Response;
use App\Services\Operations\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    public function __construct(public UserService $userService)
    {}
    public function showProfile(): JsonResponse
    {
        $result = $this->userService->getUserProfile();

        return Response::Success(
            data: new UserResource($result),
            message: __('User profile retrieved successfully'),
        );
    }

    public function editProfile(EditProfileRequest $request): JsonResponse
    {
        $dto = EditProfileDTO::fromArray($request->validated());
        $result = $this->userService->editUserProfile($dto);

        return Response::Success(
            data: new UserResource($result['user']),
            message: __('User profile updated successfully'),
        );
    }
}
