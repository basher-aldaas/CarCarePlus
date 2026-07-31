<?php

namespace App\Repositories\Eloquent;

use App\DTOs\AdminDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AdminRepository
{
    public function getAll(): Collection
    {
        return User::role('admin')
            ->latest('id')
            ->get();
    }

    public function findById(int $id): User
    {
        return User::role('admin')
            ->findOrFail($id);
    }

    public function create(AdminDTO $dto): User
    {
        return User::create(
            $dto->toArray()
        );
    }

    public function update(User $admin, AdminDTO $dto): User
    {
        // نتأكد مرة أخرى أن المستخدم Admin
        $admin = $this->findById($admin->id);

        $admin->update(
            $dto->toArray()
        );

        return $admin->refresh();
    }

    public function delete(User $admin): bool
    {
        // نتأكد مرة أخرى أن المستخدم Admin
        $admin = $this->findById($admin->id);

        return $admin->delete();
    }

    public function deactivate(User $admin): User
    {
        // نتأكد مرة أخرى أن المستخدم Admin
        $admin = $this->findById($admin->id);

        $admin->update(['is_active' => false]);

        return $admin->refresh();
    }

    public function activate(User $admin): User
    {
        // نتأكد مرة أخرى أن المستخدم Admin
        $admin = $this->findById($admin->id);

        $admin->update(['is_active' => true]);

        return $admin->refresh();
    }
}
