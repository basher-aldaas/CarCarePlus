<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\AuthDTOs\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\Response;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $dto = LoginDTO::fromArray($request->validated());
        $result = $this->authService->login($dto, $request->ip(), $request->userAgent());

        $userData = (new UserResource($result['user']))
            ->additional(['token' => $result['token']]);

        return Response::Success(
            data: $userData,
            message: __('Logged in successfully')
        );
    }
}
