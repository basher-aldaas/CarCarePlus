<?php

namespace App\Services\Operations\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

interface PaymentMethodHandlerInterface
{
    /**
     * Check the customer can actually pay $totalAmountForGroup (the sum
     * across every car in this booking submission). Throws
     * ValidationException on failure.
     */
    public function validate(User $customer, float $totalAmountForGroup, array $context): void;

    /**
     * Charge for a single order (one car) and record the Payment.
     */
    public function settle(Order $order, float $amount, array $context): Payment;

    /**
     * Reverse a single settled Payment when its order is cancelled: return
     * the value to wherever it came from (wallet balance, points, package
     * use) and mark the Payment refunded. A no-op if it's already refunded.
     */
    public function refund(Order $order, Payment $payment): void;

    /**
     * Reduce a still-pending order's Payment by a $discount granted on it.
     * Cash simply lowers the amount still owed; already-charged methods
     * (wallet, points) additionally return the discounted difference to the
     * customer's balance. $discount is a positive money amount already
     * validated against the order total.
     */
    public function adjustForDiscount(Order $order, Payment $payment, float $discount): void;
}
