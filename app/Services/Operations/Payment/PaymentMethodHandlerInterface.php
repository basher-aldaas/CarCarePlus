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
}
