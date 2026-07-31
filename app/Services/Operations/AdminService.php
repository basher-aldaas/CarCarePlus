<?php

namespace App\Services\Operations;

use App\DTOs\AdminDTO;
use App\Models\User;
use App\Repositories\Eloquent\AdminRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AdminService
{
    public function __construct(
        protected AdminRepository $adminRepository,
    ) {
    }

    public function index(): Collection
    {
        return $this->adminRepository->getAll();
    }

    public function show(int $id): User
    {
        return $this->adminRepository->findById($id);
    }

    public function store(AdminDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {

            $admin = $this->adminRepository->create($dto);

            $admin->assignRole('admin');

            return $admin->refresh();
        });
    }

    public function update(User $admin, AdminDTO $dto): User
    {
        return DB::transaction(function () use ($admin, $dto) {

            $admin = $this->adminRepository->update(
                $admin,
                $dto
            );

            return $admin->refresh();
        });
    }

    public function destroy(User $admin): bool
    {
        return DB::transaction(function () use ($admin) {

            return $this->adminRepository->delete($admin);
        });
    }

    public function deactivate(User $admin): User
    {
        return DB::transaction(function () use ($admin) {

            return $this->adminRepository->deactivate($admin);
        });
    }

    public function activate(User $admin): User
    {
        return DB::transaction(function () use ($admin) {

            return $this->adminRepository->activate($admin);
        });
    }
}
