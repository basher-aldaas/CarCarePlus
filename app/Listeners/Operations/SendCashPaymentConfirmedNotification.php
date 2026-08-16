<?php

namespace App\Listeners\Operations;

use App\Events\Operations\CashPaymentConfirmed;
use App\Notifications\Operations\CashPaymentConfirmedAdminNotification;
use App\Notifications\Operations\CashPaymentConfirmedNotification;

class SendCashPaymentConfirmedNotification
{
    public function handle(CashPaymentConfirmed $event): void
    {
        $payment = $event->payment;
        $payment->loadMissing('user', 'order.branch.manager');

        // Notify the customer who paid.
        $payment->user?->notify(new CashPaymentConfirmedNotification($payment));

        // Notify the admin of the booking's branch (skip if it's the same
        // person who paid, so they don't get the notice twice).
        $branchAdmin = $payment->order?->branch?->manager;

        if ($branchAdmin !== null && $branchAdmin->getKey() !== $payment->user_id) {
            $branchAdmin->notify(new CashPaymentConfirmedAdminNotification($payment));
        }
    }
}