<?php


namespace App\Repositories\Eloquent;

use App\DTOs\CustomersDTOs\CustomerDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CustomerPersonalRepository
{
    public function getAll(?int $branchId = null): Collection
    {
        $query = User::role('customer_personal')
            ->latest('id');

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    public function findById(int $id): User
    {
        return User::role('customer_personal')
            ->findOrFail($id);
    }

    public function update(User $customer, CustomerDTO $dto): User
    {
        $data = $dto->toArray();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $customer->update($data);

        return $customer->refresh();
    }

    public function delete(User $customer): bool
    {
        return $customer->delete();
    }
}
