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
    public function __construct(
        protected CustomerPersonalService $customerService
    ) {}

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
        $dto = CustomerDTO::fromArray(
            $request->validated()
        );

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
}
