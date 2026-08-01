<?php

namespace App\Services\Operations;

use App\DTOs\CustomerDTO;
use App\Models\User;
use App\Repositories\Eloquent\CustomerPersonalRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CustomerPersonalService
{
    public function __construct(
        protected CustomerPersonalRepository $customerRepository
    ) {}

    /**
     * Get all personal customers.
     *
     * Super Admin:
     *     all personal customers.
     *
     * Admin:
     *     branch/region filtering will be applied later
     *     when branch ownership is finalized.
     */
    public function index(): Collection
    {
        return $this->customerRepository->getAll();
    }

    /**
     * Show one personal customer.
     */
    public function show(User $customer): User
    {
        $this->ensureCustomer($customer);

        return $customer->refresh();
    }

    /**
     * Update one personal customer.
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
     * Delete one personal customer.
     */
    public function destroy(User $customer): bool
    {
        $this->ensureCustomer($customer);

        return DB::transaction(function () use ($customer) {
            return $this->customerRepository->delete($customer);
        });
    }

    /**
     * Make sure the target user is a personal customer.
     */
    protected function ensureCustomer(User $customer): void
    {
        abort_unless(
            $customer->hasRole('customer_personal'),
            404,
            __('Personal customer not found')
        );
    }
}
