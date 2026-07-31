<?php


namespace App\Services\Operations;

use App\DTOs\CustomersDTOs\CustomerDTO;
use App\Models\User;
use App\Repositories\Eloquent\CustomerPersonalRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CustomerPersonalService
{
    protected $customerRepository;

    public function __construct(
        CustomerPersonalRepository $customerRepository
    )
    {
        $this->customerRepository = $customerRepository;
    }

    public function index(): Collection
    {
        $branchId = auth()->user()->hasRole('admin')
            ? auth()->user()->managedBranch?->id
            : null;

        return $this->customerRepository->getAll($branchId);
    }

    public function show(User $customer): User
    {
        $this->ensureCustomer($customer);

        return $customer;
    }

    public function update(User $customer, CustomerDTO $dto): User
    {
        $this->ensureCustomer($customer);
        $this->ensureBranchAccess($customer);

        return DB::transaction(function () use ($customer, $dto) {
            return $this->customerRepository->update(
                $customer,
                $dto
            );
        });
    }

    public function destroy(User $customer): bool
    {
        $this->ensureCustomer($customer);
        $this->ensureBranchAccess($customer);

        return DB::transaction(function () use ($customer) {
            return $this->customerRepository->delete($customer);
        });
    }

    protected function ensureCustomer(User $customer): void
    {
        abort_unless(
            $customer->hasRole('customer_personal'),
            404,
            __('Personal customer not found')
        );
    }

    protected function ensureBranchAccess(User $customer): void
    {
        if (!auth()->user()->hasRole('admin')) {
            return;
        }

        abort_unless(
            $customer->branch_id === auth()->user()->managedBranch?->id,
            403,
            __('You cannot manage customers outside your branch')
        );
    }
}
