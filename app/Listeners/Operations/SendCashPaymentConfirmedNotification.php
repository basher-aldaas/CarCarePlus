<?php

namespace App\Listeners\Operations;

use App\Events\Operations\CashPaymentConfirmed;
use App\Notifications\Operations\CashPaymentConfirmedNotification;

class SendCashPaymentConfirmedNotification
{
    public function handle(CashPaymentConfirmed $event): void
    {
        $payment = $event->payment;
        $payment->loadMissing('user');

        $payment->user?->notify(new CashPaymentConfirmedNotification($payment));
    }
}