<?php

namespace App\Services\Operations\Package;

use App\DTOs\PriceBreakdownDTO;
use App\Enums\UserPackageStatus;
use App\Models\PackageService;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use App\Models\UserPackage;
use App\Repositories\Eloquent\UserPackageRepository;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Determines which of a customer's packages can pay for a booking, and — once
 * one is chosen — exactly what it covers versus what remains as cash_due.
 * A package always fully covers the main service it's linked to (that's the
 * "one use" the subscription is spent on); sub-services are covered only if
 * explicitly listed against that package (package_service_sub_services), with
 * price_override as the amount still owed for an otherwise-covered
 * sub-service (null = fully free). Materials are never package-covered.
 */
class PackageCoverageService
{
    public function __construct(protected UserPackageRepository $userPackageRepository)
    {}

    /**
     * The customer's active, unexpired, unused subscriptions that cover the
     * given service — presented so the customer can pick one before the
     * booking is priced.
     */
    public function eligiblePackagesFor(User $customer, Service $service): Collection
    {
        return $this->userPackageRepository->findEligible($customer->id, $service->id);
    }

    /**
     * Validate the customer's chosen subscription: owned by them, active,
     * unexpired, and actually covering the booked service.
     */
    public function resolveSelected(User $customer, int $userPackageId, Service $service): UserPackage
    {
        $userPackage = $this->userPackageRepository->findOwnedActive($customer->id, $userPackageId);

        if (! $userPackage) {
            throw ValidationException::withMessages([
                'user_package_id' => [__('This package subscription does not belong to you.')],
            ]);
        }

        if ($userPackage->status !== UserPackageStatus::ACTIVE || $userPackage->end_date->lt(now())) {
            throw ValidationException::withMessages([
                'user_package_id' => [__('This package is inactive or has expired.')],
            ]);
        }

        if (! $this->packageServiceFor($userPackage, $service)) {
            throw ValidationException::withMessages([
                'user_package_id' => [__('This package does not cover the selected service.')],
            ]);
        }

        return $userPackage;
    }

    /**
     * Diffs the fully-priced booking against what the chosen package covers.
     *
     * @param  Collection<int, SubService>  $subServices
     * @param  array<int, array{material_id: int, quantity: float, unit_price: float, total_price: float}>  $materials
     * @return array{
     *     service_covered_amount: float,
     *     sub_services: array<int, array{sub_service_id: int, price: float, covered: bool, covered_amount: float, cash_due: float}>,
     *     materials_cash_due: float,
     *     package_covered_amount: float,
     *     cash_due_amount: float,
     * }
     */
    public function computeCoverage(
        UserPackage $userPackage,
        Service $service,
        PriceBreakdownDTO $serviceBreakdown,
        Collection $subServices,
        array $materials,
    ): array {
        //جيب خدمات هي الباكيج
        $packageService = $this->packageServiceFor($userPackage, $service);

        // sub_service_id => price_override (null means fully covered)
        $coveredSubServices = $packageService
            ? $packageService->subServiceOverrides->where('is_active', true)->keyBy('sub_service_id')
            : collect();

        $subServiceBreakdown = $subServices->map(function (SubService $subService) use ($coveredSubServices) {
            $price = round((float) $subService->price, 2);
            $override = $coveredSubServices->get($subService->id);

            if (! $override) {
                return [
                    'sub_service_id' => $subService->id,
                    'price' => $price,
                    'covered' => false,
                    'covered_amount' => 0.0,
                    'cash_due' => $price,
                ];
            }

            $cashDue = $override->price_override !== null ? round((float) $override->price_override, 2) : 0.0;
            $cashDue = min($cashDue, $price);

            return [
                'sub_service_id' => $subService->id,
                'price' => $price,
                'covered' => true,
                'covered_amount' => round($price - $cashDue, 2),
                'cash_due' => $cashDue,
            ];
        })->values()->all();

        $serviceCoveredAmount = round($serviceBreakdown->servicePrice, 2);
        $subServicesCoveredAmount = round((float) collect($subServiceBreakdown)->sum('covered_amount'), 2);
        $subServicesCashDue = round((float) collect($subServiceBreakdown)->sum('cash_due'), 2);
        $materialsCashDue = round((float) collect($materials)->sum('total_price'), 2);

        return [
            'service_covered_amount' => $serviceCoveredAmount,
            'sub_services' => $subServiceBreakdown,
            'materials_cash_due' => $materialsCashDue,
            'package_covered_amount' => round($serviceCoveredAmount + $subServicesCoveredAmount, 2),
            'cash_due_amount' => round($subServicesCashDue + $materialsCashDue, 2),
        ];
    }

    protected function packageServiceFor(UserPackage $userPackage, Service $service): ?PackageService
    {
        return $userPackage->package->packageServices->firstWhere('service_id', $service->id);
    }
}
