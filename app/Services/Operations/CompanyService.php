<?php


namespace App\Services\Operations;


use App\Models\Company;
use App\Repositories\Eloquent\CompanyRepository;
use Illuminate\Support\Facades\DB;


class CompanyService
{


    public function __construct(
        protected CompanyRepository $repository
    )
    {
    }


    public function index()
    {

        return $this->repository->getAll();

    }


    public function show(
        Company $company
    )
    {

        return $company->load('owner');

    }


    public function myCompany(
        int $userId
    )
    {

        return $this->repository
            ->findByCustomerId($userId);

    }


    public function update(
        Company $company,
        array   $data
    )
    {


        return DB::transaction(function ()
        use ($company, $data) {

            return $this->repository
                ->update(
                    $company,
                    $data
                );

        });


    }


    public function destroy(
        Company $company
    )
    {


        return DB::transaction(function ()
        use ($company) {

            return $this->repository
                ->delete($company);

        });


    }


}
