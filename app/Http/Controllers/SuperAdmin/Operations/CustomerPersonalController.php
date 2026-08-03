<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\CustomerDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CustomersRequest\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Responses\Response;
use App\Models\User;
use App\Services\Operations\CustomerPersonalService;
use Illuminate\Http\JsonResponse;

class CustomerPersonalController extends Controller
{
    protected $customerService;

    public function __construct(
        CustomerPersonalService $customerService
    ) {
        $this->customerService = $customerService;
    }

    /**
     * List all personal customers.
     */

    public function index(): JsonResponse
    {
        return Response::Success(
            data: CustomerResource::collection(
                $this->customerService->index()
            ),
            message: __('Personal customers fetched successfully')
        );
    }

    /**
     * Show one personal customer.
     */
    public function show(User $customer): JsonResponse
    {

        $customer = $this->customerService->show($customer);

        return Response::Success(
            data: new CustomerResource($customer),
            message: __('Personal customer fetched successfully')
        );
    }

    /**
     * Update one personal customer.
     */
    public function update(
        UpdateCustomerRequest $request,
        User $customer
    ): JsonResponse {

        $user = auth()->user();

        $data = $request->validated();

        /*
        customer cannot edit is_active
        */

        if (!$user->hasRole('super_admin')) {

            unset($data['is_active']);

        }

        $dto = CustomerDTO::fromArray($data);

        $customer = $this->customerService->update(
            $customer,
            $dto
        );

        return Response::Success(
            data: new CustomerResource($customer),
            message: __('Personal customer updated successfully')
        );
    }
    /**
     * Delete one personal customer.
     */
    public function destroy(User $customer): JsonResponse
    {
        $this->customerService->destroy($customer);

        return Response::Success(
            data: [],
            message: __('Personal customer deleted successfully')
        );
    }

    public function myProfile(): JsonResponse
    {
        return Response::Success(
            data: new CustomerResource(auth()->user()->refresh()),
            message: __('Profile fetched successfully')
        );
    }

    public function updateMyProfile(
        UpdateCustomerRequest $request
    ): JsonResponse {

        $dto = CustomerDTO::fromArray(
            $request->validated()
        );

        $customer = $this->customerService->update(
            auth()->user(),
            $dto
        );

        return Response::Success(
            data: new CustomerResource($customer),
            message: __('Profile updated successfully')
        );
    }
}
