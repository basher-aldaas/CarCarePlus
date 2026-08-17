<?php

namespace App\Observers;

use App\DTOs\InventoryTransactionDTO;
use App\Enums\InventoryTransactionType;
use App\Enums\OrderEnums\OrderMaterialStatus;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Enums\PointsTransactionType;
use App\Exceptions\InsufficientStockException;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\PointsConfig;
use App\Models\PointsTransaction;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\InventoryTransactionRepository;
use App\Repositories\Eloquent\PointRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PaymentObserver
{
    public function __construct(
        protected PointRepository $pointRepository,
        protected InventoryRepository $inventoryRepository,
        protected InventoryTransactionRepository $inventoryTransactionRepository,
    ) {}

    public function created(Payment $payment): void
    {
        $this->awardPoints($payment);
        $this->deductMaterials($payment);
        $this->audit($payment, 'created', null, $this->auditableAttributes($payment->getAttributes()));
    }

    public function updated(Payment $payment): void
    {
        if ($payment->wasChanged('status')) {
            $this->awardPoints($payment);
            $this->deductMaterials($payment);
        }

        $changed = $this->auditableAttributes($payment->getChanges());

        if (! empty($changed)) {
            $old = [];
            foreach (array_keys($changed) as $key) {
                $old[$key] = $payment->getOriginal($key);
            }

            $this->audit($payment, 'updated', $old, $changed);
        }
    }

    /**
     * Write an audit trail row for a payment change. Runs inside the same
     * transaction as the payment itself, so a rolled-back payment leaves no
     * orphan audit entry. The actor is the authenticated user when there is
     * one, otherwise the payment's own owner (system/queue contexts).
     */
    protected function audit(Payment $payment, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => auth()->id() ?? $payment->user_id,
            'record_id' => $payment->id,
            'table_name' => $payment->getTable(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Strip surrogate/timestamp columns so the audit snapshot only carries
     * meaningful payment fields.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function auditableAttributes(array $attributes): array
    {
        return Arr::except($attributes, ['id', 'created_at', 'updated_at']);
    }

    /**
     * Awards loyalty points once per order, regardless of which payment
     * method settled it, the moment its payment is PAID.
     */
    protected function awardPoints(Payment $payment): void
    {
        if ($payment->status !== PaymentStatus::PAID || $payment->type !== PaymentType::ORDER) {
            return;
        }

        $alreadyEarned = PointsTransaction::where('reference_type', 'order')
            ->where('reference_id', $payment->order_id)
            ->where('type', PointsTransactionType::EARN)
            ->exists();

        if ($alreadyEarned) {
            return;
        }

        $config = PointsConfig::where('is_active', true)->first();

        if (! $config) {
            return;
        }

        $earned = (int) floor((float) $payment->amount * (float) $config->earn_per_amount);
        $earned = min($earned, $config->max_earn_per_order);

        if ($earned <= 0) {
            return;
        }

        $this->pointRepository->createTransaction(
            customer_id: $payment->user_id,
            type: PointsTransactionType::EARN,
            points: $earned,
            reference_type: 'order',
            reference_id: $payment->order_id,
            note: __('Booking Earn #:id', ['id' => $payment->order_id]),
        );
    }

    /**
     * Deducts each of the order's materials from the booked branch's
     * Inventory the moment its payment is PAID, and logs the movement as an
     * InventoryTransaction. Guarded by OrderMaterialStatus rather than a
     * separate flag: only APPROVED (not yet USED) lines are processed, and
     * each is flipped to USED as it's deducted, so a second payment on the
     * same order (e.g. package + cash_due) is a no-op here.
     */
    protected function deductMaterials(Payment $payment): void
    {
        if ($payment->status !== PaymentStatus::PAID || $payment->type !== PaymentType::ORDER) {
            return;
        }

        $order = $payment->order()
            ->with(['materials' => fn ($query) => $query->where('status', OrderMaterialStatus::APPROVED)])
            ->first();

        if (! $order || ! $order->branch_id || $order->materials->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($order) {
            foreach ($order->materials as $orderMaterial) {
                $inventory = $this->inventoryRepository->firstOrCreateForBranchMaterial($order->branch_id, $orderMaterial->material_id);

                $quantityBefore = (float) $inventory->quantity;
                $quantityAfter = $quantityBefore - (float) $orderMaterial->quantity;

                if ($quantityAfter < 0) {
                    throw new InsufficientStockException();
                }

                $inventory->update(['quantity' => $quantityAfter]);

                $this->inventoryTransactionRepository->create(InventoryTransactionDTO::fromArray([
                    'branch_id' => $order->branch_id,
                    'material_id' => $orderMaterial->material_id,
                    'created_by' => $order->customer_id,
                    'type' => InventoryTransactionType::OUT->value,
                    'quantity' => (float) $orderMaterial->quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'note' => __('Order #:id material usage', ['id' => $order->id]),
                ]));

                $orderMaterial->update(['status' => OrderMaterialStatus::USED]);
            }
        });
    }
}