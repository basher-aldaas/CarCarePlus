<?php

namespace App\Repositories\Eloquent;

use App\DTOs\OrderDTO;
use App\Enums\OrderEnums\OrderStatus;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository
{
    protected array $with = ['customer', 'car', 'branch', 'employee.user', 'service', 'category', 'priceItems'];

    /**
     * Bookings scoped to the currently authenticated user's role:
     * super admin/workshop see everything (the schema has no workshop-order
     * link to scope by), an admin sees their managed branch's bookings, an
     * employee sees the bookings assigned to them, and a customer sees only
     * their own bookings.
     */
    public function getAllScoped(): Builder
    {
        $user = auth()->user();

        $query = Order::with($this->with)->latest();

        if ($user->hasAnyRole(['super_admin', 'workshop'])) {
            return $query;
        }

        if ($user->hasRole('admin')) {
            $branchIds = Branch::where('admin_id', $user->id)->pluck('id');
            return $query->whereIn('branch_id', $branchIds);
        }

        if ($user->hasAnyRole(['employee_washer', 'employee_mechanic'])) {
            return $query->where('employee_id', $user->employee?->id);
        }

        return $query->where('customer_id', $user->id);
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
}