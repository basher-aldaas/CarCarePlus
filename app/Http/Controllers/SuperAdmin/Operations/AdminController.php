<?php


namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\AdminDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\AdminRequest\CreateAdminRequest;
use App\Http\Requests\OperationsRequest\AdminRequest\UpdateAdminRequest;
use App\Http\Resources\AdminResource;
use App\Http\Responses\Response;
use App\Models\User;
use App\Services\Operations\AdminService;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function __construct(
        protected AdminService $adminService
    )
    {

    }
    public function index(): JsonResponse
    {
        return Response::Success(
            data: AdminResource::collection(
                $this->adminService->index()
            ),
            message: __('Admins fetched successfully')
        );
    }

    public function show(User $admin): JsonResponse
    {
        $admin = $this->adminService->show($admin->id);

        return Response::Success(
            data: new AdminResource($admin),
            message: __('Admin fetched successfully')
        );
    }

    public function store(CreateAdminRequest $request): JsonResponse
    {
        $dto = AdminDTO::fromArray(
            $request->validated()
        );

        $admin = $this->adminService->store($dto);

        return Response::Success(
            data: new AdminResource($admin),
            message: __('Admin created successfully'),
            code: 201
        );
    }

    public function update(
        UpdateAdminRequest $request,
        User               $admin
    ): JsonResponse
    {
        $dto = AdminDTO::fromArray(
            $request->validated()
        );

        $admin = $this->adminService->update(
            $admin,
            $dto
        );

        return Response::Success(
            data: new AdminResource($admin),
            message: __('Admin updated successfully')
        );
    }

    public function destroy(User $admin): JsonResponse
    {
        $this->adminService->destroy($admin);

        return Response::Success(
            data: [],
            message: __('Admin deleted successfully')
        );
    }

    public function deactivate(User $admin): JsonResponse
    {
        $admin = $this->adminService->deactivate($admin);

        return Response::Success(
            data: new AdminResource($admin),
            message: __('Admin deactivated successfully')
        );
    }

    public function activate(User $admin): JsonResponse
    {
        $admin = $this->adminService->activate($admin);

        return Response::Success(
            data: new AdminResource($admin),
            message: __('Admin activated successfully')
        );
    }
}
