<?php

namespace App\Services\Operations;

use App\DTOs\CustomerDTO;
use App\Models\User;
use App\Repositories\Eloquent\CustomerCompanyRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CustomerCompanyService
{
    public function __construct(
        protected CustomerCompanyRepository $customerRepository
    ) {}

    /**
     * Get all company customers with their company.
     *
     * Company registration itself is handled separately:
     * RegisterCompany -> pending -> Super Admin approval.
     */
    public function index(): Collection
    {
        return $this->customerRepository->getAll();
    }

    /**
     * Show one company customer with company information.
     */
    public function show(User $customer): User
    {
        $this->ensureCustomer($customer);

        return $customer->load('company');
    }

    /**
     * Update company customer account.
     */
    public function update(User $customer, CustomerDTO $dto): User
    {
        $this->ensureCustomer($customer);

        return DB::transaction(function () use ($customer, $dto) {
            return $this->customerRepository->update(
                $customer,
                $dto
            );
        });
    }

    /**
     * Delete company customer account.
     */
    public function destroy(User $customer): bool
    {
        $this->ensureCustomer($customer);

        return DB::transaction(function () use ($customer) {
            return $this->customerRepository->delete($customer);
        });
    }

    /**
     * Make sure the target user is a company customer.
     */
    protected function ensureCustomer(User $customer): void
    {
        abort_unless(
            $customer->hasRole('customer_company'),
            404,
            __('Company customer not found')
        );
    }
}
