<?php

namespace App\Services\Operations;

use App\DTOs\InventoryTransactionDTO;
use App\DTOs\SparePartRequestDTO;
use App\Enums\InventoryTransactionType;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Enums\SparePartRequestStatus;
use App\Events\Operations\SparePartRequestCreated;
use App\Events\Operations\SparePartRequestDecided;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\SparePartAlreadyDecided;
use App\Exceptions\SparePartRequestOrderNotOpenException;
use App\Exceptions\SparePartRequestShowUnauthorizedException;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SparePartRequest;
use App\Models\User;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\InventoryTransactionRepository;
use App\Repositories\Eloquent\SparePartRequestRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SparePartRequestService
{
    public function __construct(
        protected SparePartRequestRepository $sparePartRequestRepository,
        protected InventoryRepository $inventoryRepository,
        protected InventoryTransactionRepository $inventoryTransactionRepository,
    ) {}

    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->sparePartRequestRepository->getAllForUser(auth()->user(), $perPage);
    }

    public function show(SparePartRequest $sparePartRequest): SparePartRequest
    {
        $this->authorizeView($sparePartRequest, auth()->user());

        return $this->sparePartRequestRepository->findById($sparePartRequest);
    }

    private function authorizeView(SparePartRequest $sparePartRequest, User $user): void
    {
        $sparePartRequest->loadMissing('order');

        if ($user->hasRole('super_admin')) {
            $allowed = true;
        } elseif ($user->hasRole('admin')) {
            $branchId = Branch::where('admin_id', $user->id)->value('id');
            $allowed = $sparePartRequest->order?->branch_id === $branchId;
        } elseif ($user->hasRole(['customer_personal', 'customer_company'])) {
            $allowed = $sparePartRequest->order?->customer_id === $user->id;
        } elseif ($user->hasRole('employee_mechanic')) {
            $allowed = $sparePartRequest->employee_id === $user->employee->id;
        } else {
            $allowed = false;
        }

        if (! $allowed) {
            throw new SparePartRequestShowUnauthorizedException();
        }
    }

    public function store(SparePartRequestDTO $dto, int $employeeId): SparePartRequest
    {
        $order = Order::findOrFail($dto->order_id);

        if (in_array($order->status, [
            OrderStatus::COMPLETED,
            OrderStatus::CANCELLED,
        ], true)) {
            throw new SparePartRequestOrderNotOpenException();
        }

        return DB::transaction(function () use ($dto, $employeeId) {
            $dto->employee_id = $employeeId;
            $dto->status ??= SparePartRequestStatus::PENDING->value;

            $sparePartRequest = $this->sparePartRequestRepository->create($dto);

            SparePartRequestCreated::dispatch($sparePartRequest);

            return $sparePartRequest;
        });
    }

    public function approve(SparePartRequest $sparePartRequest, int $customerId, ?string $notes): SparePartRequest
    {
        return $this->decide($sparePartRequest, SparePartRequestStatus::APPROVED, $customerId, $notes);
    }

    public function reject(SparePartRequest $sparePartRequest, int $customerId, ?string $notes): SparePartRequest
    {
        return $this->decide($sparePartRequest, SparePartRequestStatus::REJECTED, $customerId, $notes);
    }

    private function decide(SparePartRequest $sparePartRequest, SparePartRequestStatus $status, int $customerId, ?string $notes): SparePartRequest
    {
        $sparePartRequest->loadMissing('order');


        if (!($sparePartRequest->order?->customer_id !== null && $sparePartRequest->order->customer_id === $customerId)) {
            Throw new SparePartRequestShowUnauthorizedException();
        }

        if ($sparePartRequest->status === SparePartRequestStatus::REJECTED) {
            Throw new SparePartAlreadyDecided();
        }

        $isFirstApproval = $status === SparePartRequestStatus::APPROVED
            && $sparePartRequest->status === SparePartRequestStatus::PENDING;

        return DB::transaction(function () use ($sparePartRequest, $status, $notes, $isFirstApproval) {
            $dto = SparePartRequestDTO::fromArray([
                'status' => $status->value,
                'notes' => $notes,
                'decided_at' => now()->toDateTimeString(),
            ]);

            $updated = $this->sparePartRequestRepository->update($sparePartRequest, $dto);

            if ($isFirstApproval) {
                $this->deductSparePartFromInventory($updated);
                $this->recordSparePartPayment($updated);
            }

            SparePartRequestDecided::dispatch($updated);

            return $updated;
        });
    }

    /**
     * Deduct the approved spare part from its branch's inventory and log the
     * movement as an OUT InventoryTransaction, mirroring how a paid order's
     * materials are consumed. Skipped when the order has no branch; throws
     * InsufficientStockException rather than driving stock negative.
     */
    protected function deductSparePartFromInventory(SparePartRequest $sparePartRequest): void
    {
        $sparePartRequest->loadMissing('order');

        $branchId = $sparePartRequest->order?->branch_id;

        if ($branchId === null) {
            return;
        }

        $inventory = $this->inventoryRepository->firstOrCreateForBranchMaterial(
            $branchId,
            $sparePartRequest->material_id,
        );

        $quantityBefore = (float) $inventory->quantity;
        $quantityAfter = $quantityBefore - (float) $sparePartRequest->quantity;

        if ($quantityAfter < 0) {
            throw new InsufficientStockException();
        }

        $inventory->update(['quantity' => $quantityAfter]);

        $this->inventoryTransactionRepository->create(InventoryTransactionDTO::fromArray([
            'branch_id' => $branchId,
            'material_id' => $sparePartRequest->material_id,
            'created_by' => $sparePartRequest->order->customer_id,
            'type' => InventoryTransactionType::OUT->value,
            'quantity' => (float) $sparePartRequest->quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reference_id' => (string) $sparePartRequest->id,
            'note' => __('Spare part request #:id approved', ['id' => $sparePartRequest->id]),
        ]));
    }

    /**
     * Record the customer's cash charge for an approved spare part as a
     * PENDING payment (collected physically on delivery). Uses the SPARE
     * payment type, which the PaymentObserver ignores, so it neither awards
     * loyalty points nor deducts inventory. Idempotent: re-approving a request
     * that already has a payment does not create a duplicate.
     */
    protected function recordSparePartPayment(SparePartRequest $sparePartRequest): void
    {
        if (Payment::where('spare_part_request_id', $sparePartRequest->id)->exists()) {
            return;
        }

        $sparePartRequest->loadMissing('material', 'order');

        Payment::create([
            'order_id' => $sparePartRequest->order_id,
            'user_id' => $sparePartRequest->order->customer_id,
            'spare_part_request_id' => $sparePartRequest->id,
            'payment_number' => (string) Str::uuid(),
            'type' => PaymentType::SPARE,
            'method' => PaymentMethod::CASH,
            'status' => PaymentStatus::PENDING,
            'amount' => $sparePartRequest->quantity * (float) $sparePartRequest->material->unit_price,
        ]);
    }
}
