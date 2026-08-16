<?php

namespace App\Events\Operations;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a cash payment is confirmed by staff.
 */
class CashPaymentConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}
}