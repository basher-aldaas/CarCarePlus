<?php

namespace App\Services\Operations\Booking;

use App\DTOs\RoadAssistanceDetailDTO;
use App\Enums\CarEnums\CarTypeSize;
use App\Models\Order;
use App\Models\RoadAssistanceDetail;
use App\Repositories\Eloquent\ServiceRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * car_type_size is never taken from the client — it's derived from the
 * customer's own car (via its CarType) in afterCreate(), same as
 * TowingBookingHandler.
 *
 * When problem_type_id is sent, the booked service must belong to a
 * category matched by name containing "road" (e.g. "Roadside Assistance").
 * See TowingBookingHandler for why matching is done on name, not id.
 */
class RoadAssistanceBookingHandler extends AbstractBookingTypeHandler
{
    public function __construct(protected ServiceRepository $serviceRepository)
    {}

    public function supports(array $data): bool
    {
        return isset($data['problem_type_id']);
    }

    public function validate(array $data): void
    {
        if (empty($data['problem_description'])) {
            throw ValidationException::withMessages([
                'problem_description' => [__('This field is required for a road assistance booking.')],
            ]);
        }

        $service = $this->serviceRepository->findById((int) $data['service_id']);

        if (! Str::contains(strtolower($service->category?->name ?? ''), 'roadside')) {
            throw ValidationException::withMessages([
                'service_id' => [__('The selected service does not belong to the Road Assistance category.')],
            ]);
        }
    }

    public function afterCreate(Order $order, array $data): void
    {
        $carTypeSize = $order->car?->carType
            ? CarTypeSize::fromCarTypeName($order->car->carType->name)
            : null;

        $dto = RoadAssistanceDetailDTO::fromArray([
            ...$data,
            'order_id' => $order->id,
            'car_type_size' => $carTypeSize?->value,
        ]);

        RoadAssistanceDetail::create($dto->toArray());
    }
}
