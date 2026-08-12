<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\OperationsDTO\EditProfileDTO;
use App\DTOs\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\UsersRequest\updateProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\Response;
use App\Models\User;
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

    public function show(User $user): JsonResponse
    {
        $result = $this->userService->getUserById($user);

        return Response::Success(
            data: new UserResource($result),
            message: __('User profile retrieved successfully'),
        );
    }

    public function updateProfile(updateProfileRequest $request): JsonResponse
    {
        $dto = UserDTO::fromArray($request->validated());
        $result = $this->userService->updateUserProfile($dto);

        return Response::Success(
            data: new UserResource($result['user']),
            message: __('User profile updated successfully'),
        );
    }

    public function activate(User $user): JsonResponse
    {
        return Response::Success(
            data: new UserResource($this->userService->setActiveStatus($user, true)),
            message: __('User activated successfully'),
        );
    }

    public function deactivate(User $user): JsonResponse
    {
        return Response::Success(
            data: new UserResource($this->userService->setActiveStatus($user, false)),
            message: __('User deactivated successfully'),
        );
    }
}
