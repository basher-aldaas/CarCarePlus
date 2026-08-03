<?php

namespace App\Services\Operations;

use App\DTOs\CustomerDTO;
use App\Models\User;
use App\Repositories\Eloquent\CustomerPersonalRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CustomerPersonalService
{
    protected $customerRepository;

    public function __construct(
        CustomerPersonalRepository $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

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
        $user = auth()->user();

        /*
         * Personal customer:
         * only his own account.
         */
        if ($user->hasRole('customer_personal')) {

            return User::whereKey($user->id)->get();

        }

        /*
         * Admin & Super Admin:
         * all personal customers.
         */
        return $this->customerRepository->getAll();
    }
    /**
     * Show one personal customer.
     */
    public function show(
        User $customer
    ): User
    {
        $this->ensureCustomer($customer);

        $user = auth()->user();

        /*
         * Personal customer
         * can view only himself.
         */
        if (
            $user->hasRole('customer_personal')
            &&
            $customer->id != $user->id
        ) {

            abort(
                403,
                __('Unauthorized')
            );

        }

        return $customer->refresh();
    }

    /**
     * Update one personal customer.
     */
    public function update(User $customer, CustomerDTO $dto): User
    {
        $this->ensureCustomer($customer);


        $user = auth()->user();

        if (
            $user->hasRole('customer_personal')
            &&
            $customer->id != $user->id
        ) {

            abort(403);

        }

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
