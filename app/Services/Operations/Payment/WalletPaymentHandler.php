<?php

namespace App\Services\Operations\Payment;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Enums\WalletTransactionEnums\WalletTransactionReason;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Eloquent\WalletRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletPaymentHandler implements PaymentMethodHandlerInterface
{
    public function __construct(protected WalletRepository $walletRepository)
    {}

    public function pricingIsSkipped(): bool
    {
        return false;
    }

    public function validate(User $customer, float $totalAmountForGroup, array $context): void
    {
        $balance = Wallet::where('user_id', $customer->id)->value('balance') ?? 0;

        if ($balance < $totalAmountForGroup) {
            throw ValidationException::withMessages([
                'wallet' => [__('Insufficient wallet balance')],
            ]);
        }
    }

    public function settle(Order $order, float $amount, array $context): Payment
    {
        $this->walletRepository->debit(
            userId: $order->customer_id,
            amount: $amount,
            reason: WalletTransactionReason::ORDER_PAYMENT,
            note: __('Booking #:id', ['id' => $order->id]),
        );

        return Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->customer_id,
            'payment_number' => (string) Str::uuid(),
            'type' => PaymentType::ORDER,
            'method' => PaymentMethod::WALLET,
            'status' => PaymentStatus::PAID,
            'amount' => $amount,
        ]);
    }
}
