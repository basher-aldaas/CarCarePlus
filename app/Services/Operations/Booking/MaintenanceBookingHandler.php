<?php

namespace App\Services\Operations\Booking;

use App\DTOs\MaintenanceDetailDTO;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\WorkshopStatus;
use App\Models\MaintenanceDetail;
use App\Models\Order;
use App\Repositories\Eloquent\ServiceRepository;
use App\Repositories\Eloquent\WorkshopRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Matches by the booked service's category name (e.g. "Maintenance").
 * Category is derived from service_id rather than accepted from the
 * client, so it can never mismatch the service actually being booked.
 *
 * Matching is done against the category's name text rather than its id,
 * since ids are seed-order-dependent. If categories ever get a stable
 * slug/code column, switch to matching that instead — matching free-text
 * names means renaming a category in the admin panel can silently change
 * routing.
 *
 * A maintenance booking is always cash-on-site and always tied to a
 * workshop the customer picked from the nearby-workshops list — both are
 * enforced here rather than left as generic optional fields.
 */
class MaintenanceBookingHandler extends AbstractBookingTypeHandler
{
    public function __construct(
        protected ServiceRepository $serviceRepository,
        protected WorkshopRepository $workshopRepository,
    ) {}

    public function supports(array $data): bool
    {
        if (! isset($data['service_id'])) {
            return false;
        }

        $service = $this->serviceRepository->findById((int) $data['service_id']);

        return Str::contains(strtolower($service->category?->name ?? ''), 'maintenance');
    }

    public function validate(array $data): void
    {
        if (empty($data['workshop_id'])) {
            throw ValidationException::withMessages([
                'workshop_id' => [__('Select a workshop for this maintenance booking.')],
            ]);
        }

        $workshop = $this->workshopRepository->findByIdOrNull((int) $data['workshop_id']);

        if (! $workshop || $workshop->status !== WorkshopStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'workshop_id' => [__('The selected workshop is not available for booking.')],
            ]);
        }

        if (($data['payment_method'] ?? null) !== PaymentMethod::CASH->value) {
            throw ValidationException::withMessages([
                'payment_method' => [__('Maintenance bookings are paid in cash only.')],
            ]);
        }
    }

    public function afterCreate(Order $order, array $data): void
    {
        $dto = MaintenanceDetailDTO::fromArray([
            ...$data,
            'order_id' => $order->id,
        ]);

        MaintenanceDetail::create($dto->toArray());
    }
}