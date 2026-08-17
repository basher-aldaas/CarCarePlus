<?php

namespace App\Repositories\Eloquent;

use App\DTOs\OrderDTO;
use App\Enums\OrderEnums\OrderStatus;
use App\Models\Branch;
use App\Filters\OrderFilter;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    protected array $with = ['customer', 'car', 'branch', 'workshop', 'employee.user', 'service', 'category', 'priceItems', 'subServices.subService', 'materials.material', 'payments', 'userPackage.package'];

    public function __construct(
        protected WorkshopRepository $workshopRepository,
    ) {}

    /**
     * Bookings scoped to the currently authenticated user's role: super
     * admin sees everything, an admin sees their managed branch's bookings,
     * an employee sees the bookings assigned to them, a workshop owner sees
     * only the maintenance bookings placed at their workshop, and a
     * customer sees only their own bookings.
     */


    public function getAllScoped(?OrderFilter $filter = null): Builder
    {
        $user = auth()->user();

        $query = Order::with($this->with)->latest();

        if ($user->hasRole('super_admin')) {
            // no restriction
        } elseif ($user->hasRole('admin')) {
            $branchIds = Branch::where('admin_id', $user->id)->pluck('id');

            $query->whereIn('branch_id', $branchIds);
        } elseif ($user->hasAnyRole(['employee_washer', 'employee_mechanic'])) {
            $query->where('employee_id', $user->employee?->id);
        } elseif ($user->hasRole('workshop')) {
            $workshop = $this->workshopRepository->findByOwnerId($user->id);

            if (! $workshop) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('workshop_id', $workshop->id);
        } else {
            $query->where('customer_id', $user->id);
        }

        if ($filter) {
            $query = $filter->apply($query);
        }

        return $query;
    }
    public function create(OrderDTO $DTO): Order
    {
        $order = Order::create($DTO->toArray());

        return $order->load($this->with);
    }

    public function update(OrderDTO $DTO, int $id): Order
    {
        $order = Order::findOrFail($id);

        $order->update($DTO->toArray());

        return $order->refresh()->load($this->with);
    }

    public function findById(int $id): Order
    {
        return Order::with($this->with)->findOrFail($id);
    }

    /**
     * Update the booking's status and any related columns in one shot, then
     * record the transition in the status history log.
     */
    public function changeStatus(int $id, OrderStatus $to, array $extra = [], ?int $byEmployeeId = null, ?string $note = null): Order
    {
        $order = Order::findOrFail($id);
        $from = $order->status;

        $order->update(array_merge($extra, ['status' => $to->value]));

        $order->statusHistory()->create([
            'employee_id' => $byEmployeeId,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'note' => $note,
        ]);

        return $order->refresh()->load($this->with);
    }

    /**
     * A car's Maintenance + Roadside Assistance orders, latest first —
     * matched by category name the same way the booking handlers do, since
     * category ids are seed-order-dependent.
     *
     * @return Collection<int, Order>
     */
    public function carServiceHistory(int $carId): Collection
    {
        return Order::with(array_merge(['roadAssistance.problemType','maintenanceDetail','towingDetail','reports','sparePartRequests', 'car', 'workshop', 'employee', 'service', 'category', 'subServices.subService', 'materials.material']))
            ->where('car_id', $carId)
            ->whereHas('category', function (Builder $query) {
                $query->where('name', 'like', '%maintenance%')
                    ->orWhere('name', 'like', '%towing%')
                    ->orWhere('name', 'like', '%roadside%');
            })
            ->latest()
            ->get();
    }

    /**
     * Whether this car has ever had an order at the given workshop —
     * used to gate a workshop manager's access to a car's full history.
     */
    public function carHasVisitedWorkshop(int $carId, int $workshopId): bool
    {
        return Order::where('car_id', $carId)
            ->where('workshop_id', $workshopId)
            ->exists();
    }
}
