<?php

namespace App\Services\Operations\Booking;

use App\DTOs\RoadAssistanceDetailDTO;
use App\Models\Order;
use App\Models\RoadAssistanceDetail;
use Illuminate\Validation\ValidationException;

class RoadAssistanceBookingHandler extends AbstractBookingTypeHandler
{
    public function supports(array $data): bool
    {
        return isset($data['problem_type_id']);
    }

    public function validate(array $data): void
    {
        $missing = array_filter(
            ['car_type_size', 'problem_description'],
            fn (string $field) => empty($data[$field]),
        );

        if ($missing !== []) {
            throw ValidationException::withMessages(
                array_fill_keys($missing, [__('This field is required for a road assistance booking.')])
            );
        }
    }

    public function afterCreate(Order $order, array $data): void
    {
        $dto = RoadAssistanceDetailDTO::fromArray([...$data, 'order_id' => $order->id]);

        RoadAssistanceDetail::create($dto->toArray());
    }
}
