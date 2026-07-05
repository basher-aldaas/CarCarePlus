<?php

namespace App\Services\Auth;

use App\Enums\CompanyStatus;
use App\Enums\WorkshopStatus;
use App\Models\Company;
use App\Models\Workshop;
use App\Notifications\RegistrationApprovedNotification;
use App\Notifications\RegistrationRejectedNotification;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\WorkshopRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RegistrationApprovalService
{
    public function __construct(
        protected CompanyRepository $companyRepository,
        protected WorkshopRepository $workshopRepository,
    ) {}

    /**
     * @return Collection<int, Company>
     */
    public function pendingCompanies(): Collection
    {
        return $this->companyRepository->pending();
    }

    /**
     * @return Collection<int, Workshop>
     */
    public function pendingWorkshops(): Collection
    {
        return $this->workshopRepository->pending();
    }

    public function approveCompany(Company $company): Company
    {
        $company = DB::transaction(function () use ($company) {
            $company->update([
                'status' => CompanyStatus::APPROVED,
                'is_active' => true,
            ]);

            $company->owner?->update(['is_active' => true]);

            return $company->refresh();
        });

        $company->owner?->notify(new RegistrationApprovedNotification(__('company')));

        return $company;
    }

    public function rejectCompany(Company $company, ?string $reason = null): Company
    {
        $company = DB::transaction(function () use ($company) {
            $company->update([
                'status' => CompanyStatus::REJECTED,
                'is_active' => false,
            ]);

            $company->owner?->update(['is_active' => false]);

            return $company->refresh();
        });

        $company->owner?->notify(new RegistrationRejectedNotification(__('company'), $reason));

        return $company;
    }

    public function approveWorkshop(Workshop $workshop): Workshop
    {
        $workshop = DB::transaction(function () use ($workshop) {
            $workshop->update(['status' => WorkshopStatus::APPROVED]);

            $workshop->owner?->update(['is_active' => true]);

            return $workshop->refresh();
        });

        $workshop->owner?->notify(new RegistrationApprovedNotification(__('workshop')));

        return $workshop;
    }

    public function rejectWorkshop(Workshop $workshop, ?string $reason = null): Workshop
    {
        $workshop = DB::transaction(function () use ($workshop) {
            $workshop->update(['status' => WorkshopStatus::REJECTED]);

            $workshop->owner?->update(['is_active' => false]);

            return $workshop->refresh();
        });

        $workshop->owner?->notify(new RegistrationRejectedNotification(__('workshop'), $reason));

        return $workshop;
    }
}
