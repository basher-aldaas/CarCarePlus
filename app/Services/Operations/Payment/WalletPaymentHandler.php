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
use App\Models\WalletTransaction;
use App\Repositories\Eloquent\WalletRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletPaymentHandler implements PaymentMethodHandlerInterface
{
    public function __construct(protected WalletRepository $walletRepository)
    {}

    public function validate(User $customer, float $totalAmountForGroup, array $context): void
    {
        $balance = Wallet::where('user_id', $customer->id)->value('balance') ?? 0;

        if ($balance < $totalAmountForGroup) {
            throw ValidationException::withMessages([
                'wallet' => [__('Not Enough in your wallet balance')],
            ]);
        }
    }

    public function settle(Order $order, float $amount, array $context): Payment
    {
        $this->walletRepository->debit(
            userId: $order->customer_id,
            reason: WalletTransactionReason::ORDER_PAYMENT,
            amount: $amount,
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

    /**
     * Credit the paid amount back to the customer's wallet and mark the
     * payment refunded.
     */
    public function refund(Order $order, Payment $payment): void
    {
        if ($payment->status === PaymentStatus::REFUNDED) {
            return;
        }

        $this->walletRepository->credit(
            userId: $order->customer_id,
            reason: WalletTransactionReason::REFUND,
            amount: (float) $payment->amount,
            note: __('Refund for cancelled booking #:id', ['id' => $order->id]),
        );

        $payment->update(['status' => PaymentStatus::REFUNDED]);
    }
}
