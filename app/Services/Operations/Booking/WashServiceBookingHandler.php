<?php

namespace App\Services\Operations\Booking;

use App\Repositories\Eloquent\ServiceRepository;
use Illuminate\Support\Str;

/**
 * Matches by the booked service's category name (e.g. "Car Wash"). Category
 * is derived from service_id rather than accepted from the client, so it
 * can never mismatch the service actually being booked.
 *
 * Matching is done against the category's name text rather than its id,
 * since ids are seed-order-dependent. If categories ever get a stable
 * slug/code column, switch to matching that instead — matching free-text
 * names means renaming a category in the admin panel can silently change
 * routing.
 */
class WashServiceBookingHandler extends AbstractBookingTypeHandler
{
    public function __construct(protected ServiceRepository $serviceRepository)
    {}

    public function supports(array $data): bool
    {
        if (! isset($data['service_id'])) {
            return false;
        }

        $service = $this->serviceRepository->findById((int) $data['service_id']);

        return Str::contains(strtolower($service->category?->name ?? ''), 'wash');
    }
}