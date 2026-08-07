<?php

namespace App\Services\Operations\Booking;

use App\Repositories\Eloquent\CategoryRepository;
use Illuminate\Support\Str;

/**
 * Matches by the submitted category_id's name (e.g. "Car Wash").
 * CreateBookingRequest already requires and validates category_id.
 *
 * Matching is done against the category's name text rather than its id,
 * since ids are seed-order-dependent. If categories ever get a stable
 * slug/code column, switch to matching that instead — matching free-text
 * names means renaming a category in the admin panel can silently change
 * routing.
 */
class WashServiceBookingHandler extends AbstractBookingTypeHandler
{
    public function __construct(protected CategoryRepository $categoryRepository)
    {}

    public function supports(array $data): bool
    {
        if (! isset($data['category_id'])) {
            return false;
        }

        $category = $this->categoryRepository->findById((int) $data['category_id']);

        return Str::contains(strtolower($category->name), 'wash');
    }
}