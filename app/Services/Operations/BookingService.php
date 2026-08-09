<?php

namespace App\Services\Operations;

use App\DTOs\OrderDTO;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Exceptions\BookingCancelUnauthorizedException;
use App\Exceptions\BookingEditWindowExpiredException;
use App\Exceptions\BookingShowUnauthorizedException;
use App\Exceptions\BookingUpdateUnauthorizedException;
use App\Exceptions\InvalidBookingStatusTransitionException;
use App\Models\Order;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ServiceRepository;
use App\Traits\AuthorizesResourceOwnership;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingService
{
    use AuthorizesResourceOwnership;

    protected const EDIT_WINDOW_HOURS = 2;

    public function __construct(
        protected OrderRepository $orderRepository,
        protected ServiceRepository $serviceRepository,
        protected PricingEngineService $pricingEngine,
    ) {}

    public function getAllBookings(): LengthAwarePaginator
    {
        return $this->orderRepository->getAllScoped()->paginate(10);
    }


    public function getBookingById(int $id): Order
    {
        $order = $this->orderRepository->findById($id);
        $user = auth()->user();

        $isOwner = $order->customer_id === $user->id;

        if (! $isOwner && ! $this->canManage($order)) {
            throw new BookingShowUnauthorizedException();
        }

        return $order;
    }



    /**
     * The customer may edit their own booking (until the edit window
     * closes); staff who can manage it may edit it regardless of timing.
     */
    public function updateBooking(OrderDTO $DTO, int $id): Order
    {
        $order = $this->orderRepository->findById($id);
        $user = auth()->user();
        $isOwner = $order->customer_id === $user->id;

        if (! $isOwner && ! $this->canManage($order)) {
            throw new BookingUpdateUnauthorizedException();
        }

        if ($isOwner && ! $this->canManage($order)) {
            $this->assertWithinEditWindow($order);
        }

        $updated = $this->orderRepository->update($DTO, $id);

        if ($this->pricingRelevantFieldsChanged($order, $updated)) {
            $this->repriceBooking($updated);
        }

        return $updated->fresh(['priceItems', 'payments', 'subServices.subService', 'materials.material']);
    }

    /**
     * Whether the current user may act on this booking as staff: the
     * assigned employee, the branch's admin, or a super admin/workshop.
     */
    protected function canManage(Order $order): bool
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return $order->branch_id !== null && $order->branch?->admin_id === $user->id;
        }

        return $order->employee_id !== null && $order->employee_id === $user->employee?->id;
    }

    /**
     * A booking may only be edited by its customer at least 2 hours before
     * its scheduled time (immediate bookings, with no future scheduled_at,
     * are effectively locked as soon as they're created).
     */
    protected function assertWithinEditWindow(Order $order): void
    {
        $referenceTime = $order->scheduled_at ?? $order->created_at;

        if (now()->gt($referenceTime->copy()->subHours(self::EDIT_WINDOW_HOURS))) {
            throw new BookingEditWindowExpiredException();
        }
    }

    protected function pricingRelevantFieldsChanged(Order $before, Order $after): bool
    {
        foreach (['branch_id', 'scheduled_at', 'location_lat', 'location_lng', 'distance_km'] as $field) {
            if ((string) $before->{$field} !== (string) $after->{$field}) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replaces the order's price breakdown after a price-relevant field
     * changed. A package-covered order stays covered/zero — its payment was
     * already settled against a package use, not a recomputable amount.
     */
    protected function repriceBooking(Order $order): void
    {
        $paymentMethod = $order->payments()->latest('id')->first()?->method;

        if ($paymentMethod === PaymentMethod::PACKAGE) {
            return;
        }

        $service = $this->serviceRepository->findById($order->service_id);
        $priceMultiplier = (float) ($order->car?->carType?->price_multiplier ?? 1.0);

        $breakdown = $this->pricingEngine->calculate(
            service: $service,
            isVip: (bool) $order->is_vip,
            distanceKm: $order->distance_km !== null ? (float) $order->distance_km : null,
            scheduledAt: $order->scheduled_at ?? now(),
            isImmediate: (bool) $order->booking_type,
            priceMultiplier: $priceMultiplier,
            isCompanyCustomer: $order->company_id !== null,
        );

        $order->priceItems()->delete();
        $order->priceItems()->createMany($breakdown->items);

        // sub_service_price/materials_price aren't affected by a reprice
        // (selection isn't editable), so they're carried over as-is and
        // only the service portion + combined total are recomputed.
        $servicePrice = round($breakdown->servicePrice, 2);

        $order->update([
            'discount_amount' => $breakdown->discountAmount,
            'service_price' => $servicePrice,
            'total_price' => round($servicePrice + (float) $order->sub_service_price + (float) $order->materials_price, 2),
        ]);
    }

    public function assignBooking(int $id, int $employeeId): Order
    {
        $order = $this->orderRepository->findById($id);

        if ($order->status !== OrderStatus::PENDING) {
            throw new InvalidBookingStatusTransitionException(__('Only a pending booking can be assigned.'));
        }

        return $this->orderRepository->changeStatus(
            id: $id,
            to: OrderStatus::ASSIGNED,
            extra: ['employee_id' => $employeeId, 'assigned_at' => now()],
            byEmployeeId: $employeeId,
        );
    }

    public function startBooking(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (! $this->canManage($order)) {
            throw new BookingUpdateUnauthorizedException();
        }

        if ($order->status !== OrderStatus::ASSIGNED) {
            throw new InvalidBookingStatusTransitionException(__('Only an assigned booking can be started.'));
        }

        return $this->orderRepository->changeStatus(
            id: $id,
            to: OrderStatus::IN_PROGRESS,
            extra: ['started_at' => now()],
            byEmployeeId: $order->employee_id,
        );
    }

    public function completeBooking(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (! $this->canManage($order)) {
            throw new BookingUpdateUnauthorizedException();
        }

        if ($order->status !== OrderStatus::IN_PROGRESS) {
            throw new InvalidBookingStatusTransitionException(__('Only a booking in progress can be completed.'));
        }

        return $this->orderRepository->changeStatus(
            id: $id,
            to: OrderStatus::COMPLETED,
            extra: ['completed_at' => now()],
            byEmployeeId: $order->employee_id,
        );
    }

    public function cancelBooking(int $id, ?string $reason): Order
    {
        $order = $this->orderRepository->findById($id);
        $user = auth()->user();

        $this->authorizeOwnerOrRoles(
            ownerId: $order->customer_id,
            allowedRoles: ['super_admin', 'admin'],
            exception: new BookingCancelUnauthorizedException(),
        );

        if (in_array($order->status, [OrderStatus::COMPLETED, OrderStatus::CANCELLED], true)) {
            throw new InvalidBookingStatusTransitionException(__('This booking can no longer be cancelled.'));
        }

        return $this->orderRepository->changeStatus(
            id: $id,
            to: OrderStatus::CANCELLED,
            extra: ['cancelled_at' => now(), 'cancel_reason' => $reason],
            byEmployeeId: $user->employee?->id,
        );
    }
}
