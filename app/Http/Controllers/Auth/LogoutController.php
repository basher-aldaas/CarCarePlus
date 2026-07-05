<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return Response::Success(
            data: null,
            message: __('Logged out successfully')
        );
    }
}
