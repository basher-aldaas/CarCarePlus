<?php

namespace App\Services\Operations\Booking;

use App\DTOs\TowingDetailDTO;
use App\Enums\CarEnums\CarTypeSize;
use App\Models\Order;
use App\Models\TowingDetail;
use App\Repositories\Eloquent\ServiceRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Matches by the booked service's category name (e.g. "Flatbed Towing").
 * Category is derived from service_id rather than accepted from the
 * client, so it can never mismatch the service actually being booked.
 *
 * Matching is done against the category's name text rather than its id,
 * since ids are seed-order-dependent. If categories ever get a stable
 * slug/code column, switch to matching that instead.
 *
 * car_type_size is never taken from the client — it's derived from the
 * customer's own car (via its CarType) in afterCreate(), same as
 * RoadAssistanceBookingHandler.
 */
class TowingBookingHandler extends AbstractBookingTypeHandler
{
    public function __construct(protected ServiceRepository $serviceRepository)
    {}

    public function supports(array $data): bool
    {
        if (! isset($data['service_id'])) {
            return false;
        }

        $service = $this->serviceRepository->findById((int) $data['service_id']);

        return Str::contains(strtolower($service->category?->name ?? ''), 'towing');
    }

    public function validate(array $data): void
    {
        if (empty($data['destination_address'])) {
            throw ValidationException::withMessages([
                'destination_address' => [__('This field is required for a flatbed towing booking.')],
            ]);
        }
    }

    public function afterCreate(Order $order, array $data): void
    {
        $carTypeSize = $order->car?->carType
            ? CarTypeSize::fromCarTypeName($order->car->carType->name)
            : null;

        $dto = TowingDetailDTO::fromArray([
            ...$data,
            'order_id' => $order->id,
            'car_type_size' => $carTypeSize?->value,
        ]);

        TowingDetail::create($dto->toArray());
    }
}
