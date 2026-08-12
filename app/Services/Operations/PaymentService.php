<?php

namespace App\Services\Operations;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Workshop;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Payments scoped to the caller: super admin/admin see all, a workshop
     * owner sees payments on their workshop's orders, everyone else sees only
     * their own.
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->scopedQuery()
            ->with(['order', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function show(int $id): Payment
    {
        $payment = Payment::with(['order', 'user', 'cashConfirmer', 'refunds'])->findOrFail($id);

        $this->assertCanView($payment);

        return $payment;
    }

    /**
     * Staff confirm that a pending CASH payment was collected: mark it paid
     * and record who confirmed it.
     */
    public function confirmCash(int $id): Payment
    {
        $payment = Payment::with('order')->findOrFail($id);

        if ($payment->method !== PaymentMethod::CASH) {
            throw ValidationException::withMessages([
                'payment' => [__('Only cash payments can be confirmed this way.')],
            ]);
        }

        if ($payment->status !== PaymentStatus::PENDING) {
            throw ValidationException::withMessages([
                'payment' => [__('Only a pending payment can be confirmed.')],
            ]);
        }

        $this->assertCanConfirmCash($payment);

        $payment->update([
            'status' => PaymentStatus::PAID,
            'cash_confirmed_by' => auth()->id(),
        ]);

        return $payment->refresh()->load(['order', 'user', 'cashConfirmer']);
    }

    /** @return Builder<Payment> */
    protected function scopedQuery(): Builder
    {
        $user = auth()->user();

        $query = Payment::query();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $query;
        }

        if ($user->hasRole('workshop')) {
            $workshopId = Workshop::where('user_id', $user->id)->value('id');

            return $query->whereHas('order', fn (Builder $q) => $q->where('workshop_id', $workshopId));
        }

        return $query->where('user_id', $user->id);
    }

    protected function assertCanView(Payment $payment): void
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return;
        }

        if ($user->hasRole('workshop')) {
            $workshopId = Workshop::where('user_id', $user->id)->value('id');

            if ($payment->order && $payment->order->workshop_id === $workshopId) {
                return;
            }
        }

        if ($payment->user_id === $user->id) {
            return;
        }

        throw new AuthorizationException(__('Unauthorized'));
    }

    /**
     * An employee may only confirm cash on an order assigned to them.
     */
    protected function assertCanConfirmCash(Payment $payment): void
    {
        $order = $payment->order;

        $employeeId = Employee::where('user_id', auth()->id())->value('id');

        if ($order && $employeeId !== null && $order->employee_id === $employeeId) {
            return;
        }

        throw new AuthorizationException(__('You are not allowed to confirm this payment.'));
    }
}
