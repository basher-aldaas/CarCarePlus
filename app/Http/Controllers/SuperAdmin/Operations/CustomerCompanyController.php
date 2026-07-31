<?php


namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\CustomersDTOs\CustomerDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CustomersRequest\UpdateCustomerRequest;
use App\Http\Resources\CustomerCompanyResource;
use App\Http\Responses\Response;
use App\Models\User;
use App\Services\Operations\CustomerCompanyService;
use Illuminate\Http\JsonResponse;

class CustomerCompanyController extends Controller
{
    public function __construct(
        protected CustomerCompanyService $customerCompanyService
    )
    {
    }

    public function index(): JsonResponse
    {
        return Response::Success(
            data: CustomerCompanyResource::collection(
                $this->customerCompanyService->index()
            ),
            message: __('Company customers fetched successfully')
        );
    }

    public function show(User $customer): JsonResponse
    {
        $customer = $this->customerCompanyService->show($customer);

        return Response::Success(
            data: new CustomerCompanyResource($customer),
            message: __('Company customer fetched successfully')
        );
    }

    public function update(
        UpdateCustomerRequest $request,
        User                  $customer
    ): JsonResponse
    {
        $dto = CustomerDTO::fromArray($request->validated());

        $customer = $this->customerCompanyService->update(
            $customer,
            $dto
        );

        return Response::Success(
            data: new CustomerCompanyResource($customer),
            message: __('Company customer updated successfully')
        );
    }

    public function destroy(User $customer): JsonResponse
    {
        $this->customerCompanyService->destroy($customer);

        return Response::Success(
            data: [],
            message: __('Company customer deleted successfully')
        );
    }
}
