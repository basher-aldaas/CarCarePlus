<?php

namespace App\Services\Operations;

use App\DTOs\BranchesDTO;
use App\Models\Branch;
use App\Repositories\Eloquent\BranchRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BranchService
{
    public function __construct(protected BranchRepository $branchRepository)
    {}
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->branchRepository->getAll($perPage);
    }

    public function show(Branch $branch): Branch
    {
        return $this->branchRepository->findById($branch);
    }

    public function store(BranchesDTO $dto): Branch
    {
        return DB::transaction(function () use($dto){
            return $this->branchRepository->create($dto);
        });
    }

    public function update(Branch $branch, BranchesDTO $dto): Branch
    {
        return DB::transaction(function () use ($branch, $dto) {
            $user = auth()->user();

            // Super Admin can update any branch
            if ($user->hasRole('super_admin')) {
                return $this->branchRepository->update($branch, $dto);
            }

            // Admin can update only his own branch
            if ( $user->hasRole('admin') && $user->id === $branch->admin_id ) {
                return $this->branchRepository->update($branch, $dto);
            }
            throw new AccessDeniedHttpException( 'You are not allowed to update this branch.' );

        });
    }

    public function delete(Branch $branch): bool
    {
        return DB::transaction(function () use($branch){
            return $this->branchRepository->delete($branch);
        });
    }
}
