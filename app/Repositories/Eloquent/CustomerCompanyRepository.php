<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CustomerDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CustomerCompanyRepository
{
    /**
     * Get all company customers with their company.
     */
    public function getAll(): Collection
    {
        return User::role('customer_company')
            ->with('company')
            ->latest('id')
            ->get();
    }

    /**
     * Find a company customer by ID.
     */
    public function findById(int $id): User
    {
        return User::role('customer_company')
            ->with('company')
            ->findOrFail($id);
    }

    /**
     * Update company customer account.
     */
    public function update(User $customer, CustomerDTO $dto): User
    {
        $data = $dto->toArray();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $customer->update($data);

        return $customer->refresh()->load('company');
    }

    /**
     * Delete company customer account.
     */
    public function delete(User $customer): bool
    {
        return (bool) $customer->delete();
    }
}
