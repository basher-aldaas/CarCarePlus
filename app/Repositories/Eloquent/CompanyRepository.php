<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CompanyDTO;
use App\Enums\CompanyStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class CompanyRepository
{
    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    public function create(
        CompanyDTO $dto
    ): Company
    {
        return Company::create(
            $dto->toArray()
        );
    }

    public function pending(): Collection
    {
        return Company::with('owner')
            ->where(
                'status',
                CompanyStatus::PENDING
            )
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function getAll(): Collection
    {
        return Company::with('owner')
            ->latest()
            ->get();
    }

    public function findById(
        int $id
    ): Company
    {
        return Company::with('owner')
            ->findOrFail($id);
    }

    public function findByCustomerId(
        int $customerId
    ): ?Company
    {
        return Company::with('owner')
            ->where(
                'customer_id',
                $customerId
            )
            ->first();
    }

    public function update(
        Company $company,
        array $data
    ): Company
    {
        $company->update($data);

        return $company
            ->refresh()
            ->load('owner');
    }

    public function delete(
        Company $company
    ): bool
    {
        return (bool) $company->delete();
    }
}
