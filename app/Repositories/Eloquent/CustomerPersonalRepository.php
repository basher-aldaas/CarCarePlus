<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CustomerDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CustomerPersonalRepository
{
    /**
     * Get all personal customers.
     */
    public function getAll(): Collection
    {
        return User::role('customer_personal')
            ->latest('id')
            ->get();
    }

    /**
     * Find a personal customer by ID.
     */
    public function findById(int $id): User
    {
        return User::role('customer_personal')
            ->findOrFail($id);
    }

    /**
     * Update personal customer.
     */
    public function update(User $customer, CustomerDTO $dto): User
    {
        $data = $dto->toArray();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $customer->update($data);

        return $customer->refresh();
    }

    /**
     * Delete personal customer.
     */
    public function delete(User $customer): bool
    {
        return (bool) $customer->delete();
    }
}
