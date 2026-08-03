<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\WorkshopDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\WorkshopRequest\CreateWorkshopRequest;
use App\Http\Requests\OperationsRequest\WorkshopRequest\UpdateWorkshopRequest;
use App\Http\Resources\WorkshopResource;
use App\Http\Responses\Response;
use App\Models\Workshop;
use App\Services\Operations\WorkshopService;
use Illuminate\Http\JsonResponse;

class WorkshopController extends Controller
{
    protected $workshopService;

    public function __construct(
        WorkshopService $workshopService
    ) {
        $this->workshopService = $workshopService;
    }

    /**
     * List all workshops.
     */
    public function index(): JsonResponse
    {
        return Response::Success(
            data: WorkshopResource::collection(
                $this->workshopService->index()
            ),
            message: __('Workshops retrieved successfully')
        );
    }

    /**
     * Show one workshop.
     *
     * Super Admin => any workshop
     * Admin       => any workshop (read only)
     * Owner       => only his workshop
     */
    public function show(Workshop $workshop): JsonResponse
    {
        $user = auth()->user();

        if (
            !$user->hasAnyRole(['super_admin', 'admin']) &&
            $workshop->user_id != $user->id
        ) {
            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: 403
            );
        }

        return Response::Success(
            data: new WorkshopResource(
                $workshop->load('owner')
            ),
            message: __('Workshop retrieved successfully')
        );
    }

    /**
     * Create workshop.
     *
     * Normally used only by Super Admin.
     */
    public function store(CreateWorkshopRequest $request): JsonResponse
    {
        $dto = WorkshopDTO::fromArray(
            $request->validated()
        );

        $workshop = $this->workshopService->store($dto);

        return Response::Success(
            data: new WorkshopResource(
                $workshop->load('owner')
            ),
            message: __('Workshop created successfully'),
            code: 201
        );
    }

    /**
     * Update workshop.
     *
     * Super Admin => any workshop.
     * Workshop Owner => only his workshop.
     * Admin => forbidden.
     */
    public function update(
        UpdateWorkshopRequest $request,
        Workshop $workshop
    ): JsonResponse
    {
        $user = auth()->user();

        /*
         * Admin cannot edit workshops.
         */
        if ($user->hasRole('admin')) {

            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: 403
            );

        }

        /*
         * Workshop owner may edit only his workshop.
         */
        if (
            !$user->hasRole('super_admin') &&
            $workshop->user_id != $user->id
        ) {

            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: 403
            );

        }

        $dto = WorkshopDTO::fromArray(
            $request->validated()
        );

        $workshop = $this->workshopService->update(
            $workshop,
            $dto
        );

        return Response::Success(
            data: new WorkshopResource(
                $workshop->load('owner')
            ),
            message: __('Workshop updated successfully')
        );
    }

    /**
     * Delete workshop.
     *
     * Super Admin only.
     */
    public function destroy(
        Workshop $workshop
    ): JsonResponse
    {
        if (!auth()->user()->hasRole('super_admin')) {

            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: 403
            );

        }

        $this->workshopService->destroy($workshop);

        return Response::Success(
            data: [],
            message: __('Workshop deleted successfully')
        );
    }

    /**
     * Logged workshop owner profile.
     */
    public function myWorkshop(): JsonResponse
    {
        $workshop = $this->workshopService
            ->myWorkshop(auth()->id());

        if (!$workshop) {

            return Response::Error(
                data: null,
                message: __('Workshop not found'),
                code: 404
            );

        }

        return Response::Success(
            data: new WorkshopResource(
                $workshop->load('owner')
            ),
            message: __('Workshop retrieved successfully')
        );
    }
}
