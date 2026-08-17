<?php

namespace App\Services\Operations;

use App\DTOs\InventoryTransactionDTO;
use App\DTOs\OrderDTO;
use App\Enums\InventoryTransactionType;
use App\Enums\OrderEnums\OrderMaterialStatus;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\OrderEnums\OrderSubServiceStatus;
use App\Enums\PointsTransactionType;
use App\Events\Operations\BookingStatusChanged;
use App\Exceptions\BookingCancelUnauthorizedException;
use App\Exceptions\BookingEditWindowExpiredException;
use App\Exceptions\BookingShowUnauthorizedException;
use App\Exceptions\BookingUpdateUnauthorizedException;
use App\Exceptions\InvalidBookingStatusTransitionException;
use App\Exceptions\InvalidOrderDiscountException;
use App\Exceptions\TowingDetailNotFoundException;
use App\Models\Order;
use App\Notifications\Operations\OrderDiscountedNotification;
use App\Models\PointsTransaction;
use App\Models\SubService;
use App\Models\UserPoint;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\InventoryTransactionRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\PointRepository;
use App\Repositories\Eloquent\ServiceRepository;
use App\Services\Operations\Payment\PaymentMethodHandlerResolver;
use App\Traits\AuthorizesResourceOwnership;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    use AuthorizesResourceOwnership;

    protected const EDIT_WINDOW_HOURS = 2;

    public function __construct(
        protected OrderRepository $orderRepository,
        protected ServiceRepository $serviceRepository,
        protected PricingEngineService $pricingEngine,
        protected PaymentMethodHandlerResolver $paymentMethodHandlerResolver,
        protected InventoryRepository $inventoryRepository,
        protected InventoryTransactionRepository $inventoryTransactionRepository,
        protected PointRepository $pointRepository,
    ) {}

    public function getAllBookings(): LengthAwarePaginator
    {
        return $this->orderRepository->getAllScoped()->paginate(10);
    }


    public function getBookingById(int $id): Order
    {
        return $this->authorizeView($id);
    }

    /**
     * Load a booking and assert the caller may view it — its owning
     * customer, or any staff member who can manage it. Shared by the
     * booking's detail endpoints (status history, price items, etc.).
     */
    protected function authorizeView(int $id): Order
    {
        $order = $this->orderRepository->findById($id);
        $user = auth()->user();

        $isOwner = $order->customer_id === $user->id;

        if (! $isOwner && ! $this->canManageAll($order)) {
            throw new BookingShowUnauthorizedException();
        }

        return $order;
    }

    /**
     * The booking's status-transition log, oldest first, with the employee
     * (and their user) who performed each transition eager-loaded.
     */
    public function getStatusHistory(int $id): Collection
    {
        return $this->authorizeView($id)
            ->statusHistory()
            ->with('employee.user')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * The booking's price breakdown line items (service, VIP, distance, …).
     */
    public function getPriceItems(int $id): array
    {
        $order = $this->authorizeView($id);

        return [
            'items' =>$order->priceItems()->orderBy('created_at')->get(),
            'total_items_price' => $order->service_price,
        ];
    }

    /**
     * The sub-services selected on the booking (each with its sub-service),
     * together with the booking's stored sub-services total.
     *
     * @return array{items: Collection, total_price: string|null}
     */
    public function getSubServices(int $id): array
    {
        $order = $this->authorizeView($id);

        return [
            'items' => $order->subServices()->with('subService')->get(),
            'total_sub_service_price' => $order->sub_service_price,
        ];
    }

    /**
     * The materials consumed/requested on the booking (each with the material
     * and requester), together with the booking's stored materials total.
     *
     * @return array{items: Collection, total_price: string|null}
     */
    public function getMaterials(int $id): array
    {
        $order = $this->authorizeView($id);

        return [
            'items' => $order->materials()->with(['material', 'requester'])->get(),
            'total_materials_price' => $order->materials_price,
        ];
    }


    public function getMaintenanceDetail(int $id): ?\App\Models\MaintenanceDetail
    {
        return $this->authorizeView($id)->maintenanceDetail()->with('workshop')->first();
    }

    public function updateMaintenanceDetail(int $id, array $data): \App\Models\MaintenanceDetail
    {
        $order = $this->authorizeManage($id);

        $order->maintenanceDetail()->updateOrCreate(
            ['order_id' => $order->id],
            $this->only($data, ['workshop_id', 'notes']),
        );

        return $order->maintenanceDetail()->with('workshop')->firstOrFail();
    }

    public function getRoadDetail(int $id): ?\App\Models\RoadAssistanceDetail
    {
        return $this->authorizeView($id)->roadAssistance()->with('problemType')->first();
    }

    public function updateRoadDetail(int $id, array $data): \App\Models\RoadAssistanceDetail
    {
        $order = $this->authorizeManage($id);

        $order->roadAssistance()->updateOrCreate(
            ['order_id' => $order->id],
            $this->only($data, [
                'problem_type_id', 'car_type_size', 'problem_description', 'problem_image_url', 'ai_diagnosis',
            ]),
        );

        return $order->roadAssistance()->with('problemType')->firstOrFail();
    }

    public function getTowingDetail(int $id): ?\App\Models\TowingDetail
    {
        return $this->authorizeView($id)->towingDetail()->first();
    }

    public function updateTowingDetail(int $id, array $data): \App\Models\TowingDetail
    {
        $order = $this->authorizeManage($id);

        $order->towingDetail()->updateOrCreate(
            ['order_id' => $order->id],
            $this->only($data, [
                'car_type_size', 'destination_lat', 'destination_lng', 'destination_address', 'notes',
            ]),
        );

        return $order->towingDetail()->firstOrFail();
    }

    /**
     * The towing/flatbed driver, once they've reached the delivery point,
     * records the actual GPS coordinates of where the car was dropped off.
     * Only the booking's staff (its assigned employee — the maintenance
     * employee driving it — its branch admin, or a super admin) may do this,
     * and only on a booking that actually has a towing detail.
     */
    public function submitTowingDestination(int $id, array $data): \App\Models\TowingDetail
    {
        $order = $this->authorizeManage($id);

        $detail = $order->towingDetail()->first();

        if ($detail === null) {
            throw new TowingDetailNotFoundException();
        }

        $detail->update($this->only($data, ['destination_lat', 'destination_lng']));

        return $detail->refresh();
    }

    /**
     * Load a booking and assert the caller may manage it as staff (the
     * assigned employee, the branch's admin, or a super admin). Used by the
     * detail-record write endpoints, which customers may not perform.
     */
    protected function authorizeManage(int $id): Order
    {
        $order = $this->orderRepository->findById($id);
        $user = auth()->user();

        $allowed = $user->hasAnyRole(['super_admin', 'admin', 'workshop'])
            || ($order->employee_id !== null && $order->employee_id === $user->employee?->id);

        if (! $allowed) {
            throw new BookingUpdateUnauthorizedException();
        }

        return $order;
    }

    /** Keep only the given keys that are actually present in the input. */
    private function only(array $data, array $keys): array
    {
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * The customer may edit their own booking (until the edit window
     * closes); staff who can manage it may edit it regardless of timing.
     */
    public function updateBooking(OrderDTO $DTO, int $id, ?array $subServiceIds = null): Order
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

        // Only these count as a change (null on the DTO / null $subServiceIds
        // means "not submitted", not "set to null").
        $wantsVipChange = $DTO->is_vip !== null && (bool) $DTO->is_vip !== (bool) $order->is_vip;
        $wantsBookingTypeChange = $DTO->booking_type !== null && (bool) $DTO->booking_type !== (bool) $order->booking_type;
        $wantsSubServiceChange = $subServiceIds !== null;

        // A package booking's value is settled against a consumed package use,
        // not a recomputable price, so its priced inputs (VIP, immediate
        // surcharge, sub-services) can no longer be edited. Rescheduling — which
        // never re-prices a package order — is still allowed.
        if ($this->isPackageBooking($order) && ($wantsVipChange || $wantsBookingTypeChange || $wantsSubServiceChange)) {
            throw ValidationException::withMessages([
                'payment' => [__('This booking is covered by a package; VIP, booking type and sub-services can no longer be changed.')],
            ]);
        }

        // Keep booking_type and scheduled_at mutually consistent, exactly as at
        // booking time, but evaluated against the *effective* values so a
        // partial update (e.g. only booking_type) still validates correctly.
        $becomingImmediate = $DTO->booking_type !== null
            ? (bool) $DTO->booking_type
            : (bool) $order->booking_type;

        if ($becomingImmediate) {
            // An immediate booking carries no scheduled time: reject one if the
            // client submitted it, otherwise the stored one is cleared below.
            if ($DTO->scheduled_at !== null) {
                throw ValidationException::withMessages([
                    'scheduled_at' => [__('An immediate booking cannot have a scheduled time.')],
                ]);
            }
        } else {
            // A scheduled booking must end up with a time — either the one just
            // submitted or the one already stored.
            $effectiveScheduledAt = $DTO->scheduled_at ?? $order->scheduled_at;

            if ($effectiveScheduledAt === null) {
                throw ValidationException::withMessages([
                    'scheduled_at' => [__('A scheduled booking requires a scheduled time.')],
                ]);
            }
        }

        // Validate the new sub-service selection up front — an invalid id must
        // abort the whole update before anything is written.
        $resolvedSubServices = $wantsSubServiceChange
            ? $this->resolveSubServicesForOrder($order, $subServiceIds)
            : null;

        DB::transaction(function () use ($DTO, $id, $order, $becomingImmediate, $wantsSubServiceChange, $resolvedSubServices) {
            $updated = $this->orderRepository->update($DTO, $id);

            // Switching to immediate drops any stored schedule. The DTO can't
            // carry this (array_filter strips nulls), so it's cleared here.
            if ($becomingImmediate && $updated->scheduled_at !== null) {
                $updated->update(['scheduled_at' => null]);
            }

            // Full-replace the sub-service selection and re-total its price.
            if ($wantsSubServiceChange) {
                $updated->subServices()->delete();

                if ($resolvedSubServices->isNotEmpty()) {
                    $updated->subServices()->createMany(
                        $resolvedSubServices->map(fn (SubService $subService) => [
                            'sub_service_id' => $subService->id,
                            'price' => (float) $subService->price,
                            'covered_by_package' => false,
                            'status' => OrderSubServiceStatus::PENDING->value,
                        ])->all()
                    );
                }

                $updated->update([
                    'sub_service_price' => round(
                        (float) $resolvedSubServices->sum(fn (SubService $subService) => (float) $subService->price),
                        2
                    ),
                ]);
            }

            // Reprice when a service-pricing input changed, or when the
            // sub-service total moved (which shifts the combined total).
            if ($this->pricingRelevantFieldsChanged($order, $updated) || $wantsSubServiceChange) {
                $this->repriceBooking($updated);
            }
        });

        // Re-fetch with the full relation set so the response shows the same
        // rich data as the other booking endpoints (customer, car, branch,
        // service, price items, sub-services, materials, payments, …).
        return $this->orderRepository->findById($id);
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


    protected function canManageSuperAdminAndAdmin(Order $order): bool
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return $order->branch_id !== null && $order->branch?->admin_id === $user->id;
        }

        return false;

    }


    protected function canManageSuperAdminAndAdminAndCustomer(Order $order): bool
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return $order->branch_id !== null && $order->branch?->admin_id === $user->id;
        }

        if ($user->hasRole(['customer_personal', 'customer_company'])) {
            return $order->customer_id !== null && $order->customer_id === $user->id;
        }

        return false;

    }

    protected function canManageAll(Order $order): bool
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return $order->branch_id !== null && $order->branch?->admin_id === $user->id;
        }

        if ($user->hasRole('workshop')){
            return $order->workshop_id !== null && $order->workshop_id === $user->workshop?->id;
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
        foreach (['branch_id', 'scheduled_at', 'location_lat', 'location_lng', 'distance_km', 'is_vip', 'booking_type'] as $field) {
            if ((string) $before->{$field} !== (string) $after->{$field}) {
                return true;
            }
        }

        return false;
    }

    /**
     * A booking is package-covered when it's linked to a user package. This is
     * read from the order itself, not from its latest payment: a package
     * booking with a cash-due portion also records a later CASH payment, so the
     * most recent payment method isn't a reliable signal of package coverage.
     */
    protected function isPackageBooking(Order $order): bool
    {
        return $order->user_package_id !== null;
    }

    /**
     * Sub-services the customer picked for this update, restricted to ones
     * belonging to the booking's service and active — throws if any requested
     * id doesn't match. Mirrors BookingQuoteService::resolveSubServices.
     */
    protected function resolveSubServicesForOrder(Order $order, array $subServiceIds): Collection
    {
        $subServiceIds = array_values(array_unique(array_map('intval', $subServiceIds)));

        if ($subServiceIds === []) {
            return new Collection();
        }

        $subServices = SubService::whereIn('id', $subServiceIds)
            ->where('service_id', $order->service_id)
            ->where('is_active', true)
            ->get();

        if ($subServices->count() !== count($subServiceIds)) {
            throw ValidationException::withMessages([
                'sub_service_ids' => [__('One or more selected sub-services are invalid for this service.')],
            ]);
        }

        return $subServices;
    }

    /**
     * Replaces the order's price breakdown after a price-relevant field
     * changed. A package-covered order stays covered/zero — its payment was
     * already settled against a package use, not a recomputable amount.
     */
    protected function repriceBooking(Order $order): void
    {
        // A package-covered order stays covered/zero — its payment was settled
        // against a package use, not a recomputable amount.
        if ($this->isPackageBooking($order)) {
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

        // The sub-service selection is re-totalled into sub_service_price
        // before this runs, and materials aren't editable, so both are taken
        // from the order's current values — only the service portion and the
        // combined total are recomputed here.
        $servicePrice = round($breakdown->servicePrice, 2);

        $order->update([
            'discount_amount' => $breakdown->discountAmount,
            'service_price' => $servicePrice,
            'total_price' => round($servicePrice + (float) $order->sub_service_price + (float) $order->materials_price, 2),
        ]);
    }

    /**
     * A super admin grants a discount on a still-pending order (before it is
     * assigned to an employee). The discount is either a fixed money amount or
     * a percentage of the current total; it is added to the order's recorded
     * discount and subtracted from both the total price and the cash still due,
     * then the order record is updated.
     *
     * @param array{type: string, value: float|int|string, reason?: string|null} $data
     */
    public function applyDiscount(int $id, array $data): Order
    {
        $order = $this->orderRepository->findById($id);

        if (! $this->canManageSuperAdminAndAdmin($order)) {
            throw new BookingUpdateUnauthorizedException();
        }

        if ($order->status !== OrderStatus::PENDING) {
            throw new InvalidBookingStatusTransitionException(
                __('A discount can only be applied to a pending booking, before it is assigned.')
            );
        }

        if ($order->user_package_id !== null) {
            throw new InvalidOrderDiscountException(
                __('A discount cannot be applied to a package booking.')
            );
        }

        $currentTotal = (float) $order->total_price;

        $discount = round($currentTotal * ((float) $data['value'] / 100), 2);

        if ($discount <= 0 || $discount > $currentTotal) {
            throw new InvalidOrderDiscountException();
        }

        $updatedOrder = DB::transaction(function () use ($order, $id, $discount, $currentTotal, $data) {
            $newTotal = round($currentTotal - $discount, 2);
            $newDiscount = round((float) $order->discount_amount + $discount, 2);

            $attributes = [
                'discount_amount' => $newDiscount,
                'total_price' => $newTotal,
            ];

            if (! empty($data['reason'])) {
                $attributes['notes'] = trim(($order->notes ? $order->notes . ' | ' : '')
                    . __('Discount applied: :reason', ['reason' => $data['reason']]));
            }

            $order->update($attributes);

            // Keep the recorded payment in step with the reduced total: cash
            // lowers what's still owed, while wallet/points (already charged
            // in full at confirm) return the discounted difference.
            $payment = $order->payments()->latest('id')->first();

            if ($payment !== null) {
                $this->paymentMethodHandlerResolver
                    ->resolve($payment->method)
                    ->adjustForDiscount($order, $payment, $discount);
            }

            return $this->orderRepository->findById($id);
        });

        // Let the customer know a discount was granted (in-app + email).
        $updatedOrder->customer?->notify(
            new OrderDiscountedNotification($updatedOrder, $discount, $data['reason'] ?? null)
        );

        return $updatedOrder;
    }

    public function assignBooking(int $id, int $employeeId): Order
    {
        $order = $this->orderRepository->findById($id);

        if (! $this->canManageSuperAdminAndAdmin($order)) {
            throw new BookingUpdateUnauthorizedException();
        }

        if ($order->status !== OrderStatus::PENDING) {
            throw new InvalidBookingStatusTransitionException(__('Only a pending booking can be assigned.'));
        }

        $order = $this->orderRepository->changeStatus(
            id: $id,
            to: OrderStatus::ASSIGNED,
            extra: ['employee_id' => $employeeId, 'assigned_at' => now()],
            byEmployeeId: $employeeId,
        );

        BookingStatusChanged::dispatch($order, OrderStatus::ASSIGNED);

        return $order;
    }

    public function startBooking(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (! $this->canManageAll($order)) {
            throw new BookingUpdateUnauthorizedException();
        }

        if ($order->status !== OrderStatus::ASSIGNED) {
            throw new InvalidBookingStatusTransitionException(__('Only an assigned booking can be started.'));
        }



        $order = $this->orderRepository->changeStatus(
            id: $id,
            to: OrderStatus::IN_PROGRESS,
            extra: ['started_at' => now()],
            byEmployeeId: $order->employee_id,
        );

        BookingStatusChanged::dispatch($order, OrderStatus::IN_PROGRESS);

        return $order;
    }

    public function completeBooking(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (! $this->canManageAll($order)) {
            throw new BookingUpdateUnauthorizedException();
        }

        if ($order->status !== OrderStatus::IN_PROGRESS) {
            throw new InvalidBookingStatusTransitionException(__('Only a booking in progress can be completed.'));
        }

        $order = $this->orderRepository->changeStatus(
            id: $id,
            to: OrderStatus::COMPLETED,
            extra: ['completed_at' => now()],
            byEmployeeId: $order->employee_id,
        );

        BookingStatusChanged::dispatch($order, OrderStatus::COMPLETED);

        return $order;
    }

    public function cancelBooking(int $id, ?string $reason): Order
    {
        $order = $this->orderRepository->findById($id);
        $user = auth()->user();

        $isStaff = $this->canManageSuperAdminAndAdmin($order);
        $isOwner = $order->customer_id !== null && $order->customer_id === $user->id;

        if (! $isStaff && ! $isOwner) {
            throw new BookingUpdateUnauthorizedException();
        }

        if ($isStaff) {
            // SA, A: cancel any time, unless it's already finished.
            if (in_array($order->status, [OrderStatus::COMPLETED, OrderStatus::CANCELLED], true)) {
                throw new InvalidBookingStatusTransitionException(
                    __('This booking can no longer be cancelled.')
                );
            }
        } else {
            // Customer: only while still pending...
            if ($order->status !== OrderStatus::PENDING) {
                throw new InvalidBookingStatusTransitionException(
                    __('This booking can no longer be cancelled.')
                );
            }

            // ...and no later than 30 minutes before the scheduled time.
            if ($order->scheduled_at && now()->addMinutes(30)->gte($order->scheduled_at)) {
                throw new InvalidBookingStatusTransitionException(
                    __('A booking can only be cancelled at least 30 minutes before its scheduled time.')
                );
            }
        }

        // Reverse whatever was settled at confirm time (wallet credit, points
        // returned, package use restored, cash voided), claw back any loyalty
        // points that were awarded for it, put back any material stock that
        // was already deducted, and flip the status — all or nothing.
        $cancelled = DB::transaction(function () use ($order, $id, $reason, $user) {
            // Runs before the payment refunds: a points-method refund writes
            // its own EARN row for the order, which must not be mistaken for
            // the loyalty award we're reversing here.
            $this->clawBackEarnedPoints($order);

            foreach ($order->payments as $payment) {
                $this->paymentMethodHandlerResolver
                    ->resolve($payment->method)
                    ->refund($order, $payment);
            }

            $this->restoreMaterials($order);

            return $this->orderRepository->changeStatus(
                id: $id,
                to: OrderStatus::CANCELLED,
                extra: [
                    'cancelled_at' => now(),
                    'cancel_reason' => $reason,
                ],
                byEmployeeId: $user->employee?->id,
            );
        });

        BookingStatusChanged::dispatch($cancelled, OrderStatus::CANCELLED, $reason);

        return $cancelled;
    }

    /**
     * Return material stock that was already deducted for this order back to
     * its branch's inventory when the order is cancelled. Only USED lines —
     * the ones {@see \App\Observers\PaymentObserver::deductMaterials()}
     * actually consumed once a payment was PAID — are restored and logged as
     * an IN movement, then flipped to REJECTED so they can never be
     * re-deducted. Materials still merely APPROVED (e.g. an unpaid cash
     * booking) were never taken from stock, so there's nothing to give back.
     */
    /**
     * Reverse the loyalty points that {@see \App\Observers\PaymentObserver::awardPoints()}
     * granted for this order once it was paid. The award is a single EARN row
     * keyed to the order; we redeem the same amount back out on cancel. If the
     * customer has already spent some of them, we only claw back what's still
     * in their balance rather than driving it negative or blocking the cancel.
     */
    protected function clawBackEarnedPoints(Order $order): void
    {
        $earned = (int) PointsTransaction::where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('type', PointsTransactionType::EARN)
            ->sum('points');

        if ($earned <= 0) {
            return;
        }

        $balance = (int) (UserPoint::where('customer_id', $order->customer_id)->value('balance') ?? 0);
        $clawBack = min($earned, $balance);

        if ($clawBack <= 0) {
            return;
        }

        $this->pointRepository->createTransaction(
            customer_id: $order->customer_id,
            type: PointsTransactionType::REDEEM,
            points: $clawBack,
            reference_type: 'order_cancel',
            reference_id: $order->id,
            note: __('Order #:id cancelled — earned points reversed', ['id' => $order->id]),
        );
    }

    protected function restoreMaterials(Order $order): void
    {
        if ($order->branch_id === null) {
            return;
        }

        $usedMaterials = $order->materials()
            ->where('status', OrderMaterialStatus::USED)
            ->get();

        foreach ($usedMaterials as $orderMaterial) {
            $inventory = $this->inventoryRepository->firstOrCreateForBranchMaterial(
                $order->branch_id,
                $orderMaterial->material_id,
            );

            $quantityBefore = (float) $inventory->quantity;
            $quantityAfter = $quantityBefore + (float) $orderMaterial->quantity;

            $inventory->update(['quantity' => $quantityAfter]);

            $this->inventoryTransactionRepository->create(InventoryTransactionDTO::fromArray([
                'branch_id' => $order->branch_id,
                'material_id' => $orderMaterial->material_id,
                'created_by' => auth()->id() ?? $order->customer_id,
                'type' => InventoryTransactionType::IN->value,
                'quantity' => (float) $orderMaterial->quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'note' => __('Order #:id cancelled — material returned', ['id' => $order->id]),
            ]));

            $orderMaterial->update(['status' => OrderMaterialStatus::REJECTED]);
        }
    }
}
