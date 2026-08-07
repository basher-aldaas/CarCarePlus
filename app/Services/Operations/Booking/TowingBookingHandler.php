<?php

namespace App\Services\Operations\Booking;

use App\DTOs\TowingDetailDTO;
use App\Models\Order;
use App\Models\TowingDetail;
use App\Repositories\Eloquent\CategoryRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Matches by the submitted category_id's name (e.g. "Flatbed Towing").
 * CreateBookingRequest already requires and validates category_id.
 *
 * Matching is done against the category's name text rather than its id,
 * since ids are seed-order-dependent. If categories ever get a stable
 * slug/code column, switch to matching that instead.
 */
class TowingBookingHandler extends AbstractBookingTypeHandler
{
    public function __construct(protected CategoryRepository $categoryRepository)
    {}

    public function supports(array $data): bool
    {
        if (! isset($data['category_id'])) {
            return false;
        }

        $category = $this->categoryRepository->findById((int) $data['category_id']);

        return Str::contains(strtolower($category->name), 'tow');
    }

    public function validate(array $data): void
    {
        $missing = array_filter(
            ['car_type_size', 'destination_address'],
            fn (string $field) => empty($data[$field]),
        );

        if ($missing !== []) {
            throw ValidationException::withMessages(
                array_fill_keys($missing, [__('This field is required for a flatbed towing booking.')])
            );
        }
    }

    public function afterCreate(Order $order, array $data): void
    {
        $dto = TowingDetailDTO::fromArray([...$data, 'order_id' => $order->id]);

        TowingDetail::create($dto->toArray());
    }
}