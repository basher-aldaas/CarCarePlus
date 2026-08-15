<?php

namespace App\Services\Operations\Payment;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Exceptions\InvalidOrderDiscountException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\UserPackage;
use App\Services\Operations\Package\PackageCoverageService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Settles the portion of a booking a package covers and, when the booking
 * isn't fully covered, delegates the remainder to CashPaymentHandler so the
 * customer's cash_due is charged through the normal cash flow rather than
 * silently dropped.
 */
class PackagePaymentHandler implements PaymentMethodHandlerInterface
{
    public function __construct(
        protected PackageCoverageService $packageCoverageService,
        protected CashPaymentHandler $cashPaymentHandler,
    ) {}

    public function validate(User $customer, float $totalAmountForGroup, array $context): void
    {
        /** @var Service $service */
        $service = $context['service'];
        $carCount = count($context['car_ids']);
        $userPackageId = $context['user_package_id'] ?? null;

        if (! $userPackageId) {
            throw ValidationException::withMessages([
                'user_package_id' => [__('Select which of your packages to use for this booking.')],
            ]);
        }

        $userPackage = $this->packageCoverageService->resolveSelected($customer, (int) $userPackageId, $service);

        if ($userPackage->remaining_count < $carCount) {
            throw ValidationException::withMessages([
                'user_package_id' => [__('Your package does not have enough remaining uses for this booking')],
            ]);
        }
    }

    public function settle(Order $order, float $amount, array $context): Payment
    {
        $userPackage = UserPackage::lockForUpdate()->findOrFail($context['user_package_id']);

        $userPackage->update(['remaining_count' => $userPackage->remaining_count - 1]);

        $packageCoveredAmount = round((float) ($context['package_covered_amount'] ?? 0), 2);
        $cashDueAmount = round((float) ($context['cash_due_amount'] ?? 0), 2);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->customer_id,
            'package_id' => $userPackage->package_id,
            'payment_number' => (string) Str::uuid(),
            'type' => PaymentType::ORDER,
            'method' => PaymentMethod::PACKAGE,
            'status' => PaymentStatus::PAID,
            'amount' => $packageCoveredAmount,
        ]);

        if ($cashDueAmount > 0.0) {
            $this->cashPaymentHandler->settle($order, $cashDueAmount, $context);
        }

        return $payment;
    }

    /**
     * Give the consumed package use back (+1) and mark the package payment
     * refunded. Any cash_due portion is settled as its own CASH payment row,
     * which is reversed separately by the cash handler.
     */
    public function refund(Order $order, Payment $payment): void
    {
        if ($payment->status === PaymentStatus::REFUNDED) {
            return;
        }

        if ($order->user_package_id !== null) {
            $userPackage = UserPackage::lockForUpdate()->find($order->user_package_id);

            $userPackage?->update(['remaining_count' => $userPackage->remaining_count + 1]);
        }

        $payment->update(['status' => PaymentStatus::REFUNDED]);
    }

    /**
     * Package bookings can't be discounted — their value is settled against a
     * consumed package use, not a recomputable price. Guarded here as a
     * safety net; the discount flow rejects package orders before this point.
     */
    public function adjustForDiscount(Order $order, Payment $payment, float $discount): void
    {
        throw new InvalidOrderDiscountException(
            __('A discount cannot be applied to a package booking.')
        );
    }
}