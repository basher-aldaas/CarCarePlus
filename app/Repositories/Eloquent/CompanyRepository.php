<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CompanyDTO;
use App\Enums\CompanyStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class CompanyRepository
{
    public function create(CompanyDTO $DTO): Company
    {
        return Company::create($DTO->toArray());
    }

    /**
     * @return Collection<int, Company>
     */
    public function pending(): Collection
    {
        return Company::with('owner')
            ->where('status', CompanyStatus::PENDING)
            ->latest()
            ->get();
    }
}
