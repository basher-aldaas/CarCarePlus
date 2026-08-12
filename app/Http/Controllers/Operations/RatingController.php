<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\RatingRequest\CreateRatingRequest;
use App\Http\Requests\OperationsRequest\RatingRequest\UpdateRatingRequest;
use App\Http\Resources\RatingResource;
use App\Http\Responses\Response;
use App\Services\Operations\RatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(protected RatingService $ratingService)
    {}

    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: RatingResource::collection($this->ratingService->index($request->integer('per_page', 15))),
            message: __('Ratings retrieved successfully')
        );
    }

    public function show(int $id): JsonResponse
    {
        return Response::Success(
            data: new RatingResource($this->ratingService->show($id)),
            message: __('Rating retrieved successfully')
        );
    }

    public function store(CreateRatingRequest $request): JsonResponse
    {
        return Response::Success(
            data: new RatingResource($this->ratingService->create($request->validated())),
            message: __('Rating submitted successfully')
        );
    }

    public function update(UpdateRatingRequest $request, int $id): JsonResponse
    {
        return Response::Success(
            data: new RatingResource($this->ratingService->update($id, $request->validated())),
            message: __('Rating updated successfully')
        );
    }
}