<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Services\EnumService;
use Illuminate\Http\JsonResponse;

class EnumController extends Controller
{
    public function __construct(
        protected EnumService $enumService
    ) {
    }

    /**
     * Return every application enum with its values and localized labels.
     */
    public function index(): JsonResponse
    {
        return Response::Success(
            $this->enumService->all(),
            __('Enums fetched successfully')
        );
    }
}
