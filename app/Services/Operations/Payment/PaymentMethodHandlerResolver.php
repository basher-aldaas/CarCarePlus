<?php

namespace App\Services\Operations\Payment;

use App\Enums\PaymentEnums\PaymentMethod;

class PaymentMethodHandlerResolver
{
    public function __construct(
        protected CashPaymentHandler $cashPaymentHandler,
        protected WalletPaymentHandler $walletPaymentHandler,
        protected PointsPaymentHandler $pointsPaymentHandler,
        protected PackagePaymentHandler $packagePaymentHandler,
    ) {}

    public function resolve(PaymentMethod $method): PaymentMethodHandlerInterface
    {
        return match ($method) {
            PaymentMethod::CASH => $this->cashPaymentHandler,
            PaymentMethod::WALLET => $this->walletPaymentHandler,
            PaymentMethod::POINT => $this->pointsPaymentHandler,
            PaymentMethod::PACKAGE => $this->packagePaymentHandler,
            PaymentMethod::CARD => throw new \RuntimeException('Card payment is not supported yet.'),
        };
    }
}
