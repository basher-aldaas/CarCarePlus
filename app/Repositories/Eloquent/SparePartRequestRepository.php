<?php

namespace App\Repositories\Eloquent;

use App\DTOs\SparePartRequestDTO;
use App\Models\Branch;
use App\Models\SparePartRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class SparePartRequestRepository
{

    /**
     * List the spare part requests the given user is allowed to see:
     *  - super admin & employee: every request
     *  - admin: requests whose order belongs to the branch he manages
     *  - customer: requests raised against his own orders
     *  - anyone else: nothing
     */
    public function getAllForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = SparePartRequest::with([
            'order',
            'employee.user',
            'material.unit',
        ]);

        if ($user->hasRole('super_admin')) {
            // No restriction — sees everything.
        } elseif ($user->hasRole('admin')) {
            $branch_id  = Branch::where('admin_id', $user->id)->value('id');
            $query->whereHas('order', fn ($q) => $q->where('branch_id', $branch_id));
        } elseif ($user->hasRole(['customer_personal','customer_company'])) {
            $query->whereHas('order', fn($q) =>$q->where('customer_id', $user->id));
        } elseif ( $user->hasRole('employee_mechanic')) {
            $query->where('employee_id', $user->employee->id);
        }else {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->latest('id')
            ->paginate($perPage);
    }


    public function findById(SparePartRequest $sparePartRequest): SparePartRequest
    {
        return $sparePartRequest->load(['order', 'employee.user', 'material.unit']);
    }

    public function create(SparePartRequestDTO $dto): SparePartRequest
    {
        return SparePartRequest::create($dto->toArray());
    }

    public function update(SparePartRequest $sparePartRequest, SparePartRequestDTO $dto): SparePartRequest
    {
        $sparePartRequest->update($dto->toArray());
        return $sparePartRequest->fresh(['order', 'employee.user', 'material.unit']);
    }

    public function delete(SparePartRequest $sparePartRequest): bool
    {
        return $sparePartRequest->delete();
    }
}
