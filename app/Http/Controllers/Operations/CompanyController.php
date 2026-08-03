<?php


namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CompanyRequest\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Responses\Response;
use App\Models\Company;
use App\Services\Operations\CompanyService;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{

    protected $companyService;

    public function __construct(
        CompanyService $companyService
    )
    {
        $this->companyService = $companyService;
    }


    /**
     * Admin + Super Admin
     * Show all companies
     */
    public function index(): JsonResponse
    {

        return Response::Success(
            data: CompanyResource::collection(
                $this->companyService->index()
            ),
            message: __('Companies retrieved successfully')
        );

    }


    /**
     * Company owner show his company only
     * Also SA/Admin can show specific company
     */
    public function show(
        Company $company
    ): JsonResponse
    {

        $user = auth()->user();


        if (
            !$user->hasAnyRole([
                'super_admin',
                'admin'
            ])
            &&
            $company->customer_id != $user->id
        ) {

            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: 403
            );

        }


        return Response::Success(
            data: new CompanyResource(
                $this->companyService->show($company)
            ),
            message: __('Company retrieved successfully')
        );

    }


    /**
     * Company owner profile
     */
    public function myCompany(): JsonResponse
    {

        $company =
            $this->companyService
                ->myCompany(auth()->id());


        if (!$company) {

            return Response::Error(
                data: null,
                message: __('Company not found'),
                code: 404
            );

        }


        return Response::Success(
            data: new CompanyResource($company),
            message: __('Company retrieved successfully')
        );

    }


    /**
     * Update
     *
     * Company:
     * only own data
     *
     * Super Admin:
     * everything
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): JsonResponse
    {
        $user = auth()->user();

        // Company can edit only itself
        if (
            !$user->hasRole('super_admin')
            &&
            $company->customer_id != $user->id
        ) {
            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: 403
            );
        }

        $data = $request->validated();

        /*
        Company cannot change:
        status
        is_active

        Only Super Admin
        */
        if (!$user->hasRole('super_admin')) {

            unset(
                $data['status'],
                $data['is_active']
            );

        }

        $company = $this->companyService->update(
            $company,
            $data
        );

        return Response::Success(
            data: new CompanyResource($company),
            message: __('Company updated successfully')
        );
    }

    /**
     * Delete
     *
     * Super Admin only
     */
    public function destroy(
        Company $company
    ): JsonResponse
    {

        if (!auth()->user()->hasRole('super_admin')) {


            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: 403
            );

        }


        $this->companyService
            ->destroy($company);


        return Response::Success(
            data: [],
            message: __('Company deleted successfully')
        );


    }


}
