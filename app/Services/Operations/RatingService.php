<?php

namespace App\Services\Operations;

use App\Enums\OrderEnums\OrderStatus;
use App\Models\Order;
use App\Models\Rating;
use App\Models\Workshop;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class RatingService
{
    /**
     * Ratings scoped to the caller: super admin/admin see all, a workshop
     * owner sees ratings on their workshop's orders, a customer sees their own.
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->scopedQuery()
            ->with(['customer', 'employee.user'])
            ->latest()
            ->paginate($perPage);
    }

    public function show(int $id): Rating
    {
        $rating = Rating::with(['customer', 'employee.user', 'order'])->findOrFail($id);

        $this->assertCanView($rating);

        return $rating;
    }

    /**
     * A customer rates their own completed order. One rating per order.
     */
    public function create(array $data): Rating
    {
        $order = Order::findOrFail($data['order_id']);
        $user = auth()->user();

        if ($order->customer_id !== $user->id) {
            throw new AuthorizationException(__('You may only rate your own orders.'));
        }

        if ($order->status !== OrderStatus::COMPLETED) {
            throw ValidationException::withMessages([
                'order_id' => [__('Only a completed order can be rated.')],
            ]);
        }

        if (Rating::where('order_id', $order->id)->exists()) {
            throw ValidationException::withMessages([
                'order_id' => [__('This order has already been rated.')],
            ]);
        }

        $rating = Rating::create([
            'order_id' => $order->id,
            'customer_id' => $user->id,
            'employee_id' => $order->employee_id,
            'service_rating' => $data['service_rating'],
            'employee_rating' => $data['employee_rating'] ?? null,
            'workshop_rating' => $data['workshop_rating'] ?? null,
            'comment' => $data['comment'] ?? null,
            'image_urls' => $data['image_urls'] ?? null,
        ]);

        return $rating->load(['customer', 'employee.user']);
    }

    /**
     * The rating's author may edit it (staff with manage rights may too).
     */
    public function update(int $id, array $data): Rating
    {
        $rating = Rating::findOrFail($id);
        $user = auth()->user();

        if ($rating->customer_id !== $user->id && ! $user->hasAnyRole(['super_admin', 'admin'])) {
            throw new AuthorizationException(__('Unauthorized'));
        }

        $rating->update(array_filter([
            'service_rating' => $data['service_rating'] ?? null,
            'employee_rating' => $data['employee_rating'] ?? null,
            'workshop_rating' => $data['workshop_rating'] ?? null,
            'comment' => $data['comment'] ?? null,
            'image_urls' => $data['image_urls'] ?? null,
        ], fn ($v) => $v !== null));

        return $rating->refresh()->load(['customer', 'employee.user']);
    }

    /** @return Builder<Rating> */
    protected function scopedQuery(): Builder
    {
        $user = auth()->user();

        $query = Rating::query();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $query;
        }

        if ($user->hasRole('workshop')) {
            $workshopId = Workshop::where('user_id', $user->id)->value('id');

            return $query->whereHas('order', fn (Builder $q) => $q->where('workshop_id', $workshopId));
        }

        return $query->where('customer_id', $user->id);
    }

    protected function assertCanView(Rating $rating): void
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return;
        }

        if ($user->hasRole('workshop')) {
            $workshopId = Workshop::where('user_id', $user->id)->value('id');

            if ($rating->order && $rating->order->workshop_id === $workshopId) {
                return;
            }
        }

        if ($rating->customer_id === $user->id) {
            return;
        }

        throw new AuthorizationException(__('Unauthorized'));
    }
}