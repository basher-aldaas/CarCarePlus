<?php

namespace App\Services\Operations\Payment;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\UserPackage;
use App\Repositories\Eloquent\UserPackageRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PackagePaymentHandler implements PaymentMethodHandlerInterface
{
    public function __construct(protected UserPackageRepository $userPackageRepository)
    {}

    public function pricingIsSkipped(): bool
    {
        return true;
    }

    public function validate(User $customer, float $totalAmountForGroup, array $context): void
    {
        /** @var Service $service */
        $service = $context['service'];
        $carCount = count($context['car_ids']);

        $userPackage = $this->userPackageRepository->findActiveCoveringService($customer->id, $service->id);

        if (! $userPackage) {
            throw ValidationException::withMessages([
                'payment_method' => [__('You have no active package covering this service')],
            ]);
        }

        if ($userPackage->remaining_count < $carCount) {
            throw ValidationException::withMessages([
                'payment_method' => [__('Your package does not have enough remaining uses for this booking')],
            ]);
        }
    }

    public function settle(Order $order, float $amount, array $context): Payment
    {
        /** @var Service $service */
        $service = $context['service'];

        $userPackageId = $this->userPackageRepository
            ->findActiveCoveringService($order->customer_id, $service->id)
            ?->id;

        $userPackage = UserPackage::lockForUpdate()->findOrFail($userPackageId);

        $userPackage->update(['remaining_count' => $userPackage->remaining_count - 1]);

        return Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->customer_id,
            'package_id' => $userPackage->package_id,
            'payment_number' => (string) Str::uuid(),
            'type' => PaymentType::ORDER,
            'method' => PaymentMethod::PACKAGE,
            'status' => PaymentStatus::PAID,
            'amount' => 0,
        ]);
    }
}
