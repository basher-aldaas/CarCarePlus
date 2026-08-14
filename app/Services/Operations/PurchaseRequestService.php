<?php

namespace App\Services\Operations;

use App\DTOs\PurchaseRequestDTO;
use App\DTOs\PurchaseRequestItemDTO;
use App\Enums\InventoryTransactionType;
use App\Enums\PurchaseRequestStatus;
use App\Exceptions\PurchaseRequestShowUnauthorizedException;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\PurchasePayment;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Repositories\Eloquent\PurchaseRequestItemRepository;
use App\Repositories\Eloquent\PurchaseRequestRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequestService
{
    public function __construct(
        protected PurchaseRequestRepository $purchaseRequestRepository,
        protected PurchaseRequestItemRepository $purchaseRequestItemRepository,
        protected InventoryTransactionService $inventoryTransactionService,
    )
    {}

    public function index(int $perPage = 15): LengthAwarePaginator
    {
        $user = auth()->user();
        $branch_id = Branch::where('admin_id', $user->id)->value('id');

        $branchId = $user->hasRole('super_admin') ? null : $branch_id;

        $purchaseRequest = PurchaseRequest::where('branch_id', $branchId)->get();

        return $this->purchaseRequestRepository->getAll($perPage, $purchaseRequest);
    }

    public function show(PurchaseRequest $purchaseRequest): PurchaseRequest
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return $this->purchaseRequestRepository->findById($purchaseRequest);
        }

        if ($user->hasRole('admin')) {
            $branchId = Branch::where('admin_id', $user->id)->value('id');

            if ($branchId === null || $branchId !== $purchaseRequest->branch_id) {
                throw new PurchaseRequestShowUnauthorizedException();
            }

            return $this->purchaseRequestRepository->findById($purchaseRequest);
        }

        throw new PurchaseRequestShowUnauthorizedException();
    }

    public function store(PurchaseRequestDTO $dto, array $items = []): PurchaseRequest
    {
        return DB::transaction(function () use ($dto, $items) {
            $dto->branch_id = Branch::where('admin_id', auth()->id())->value('id');
            $dto->from_branch_id = null;
            $dto->request_type = false;//means purchase ask
            $dto->status = PurchaseRequestStatus::PENDING->value;
            $dto->total_amount = 0;
            $dto->approved_at = null;
            $dto->rejection_reason = null;

            $purchaseRequest = $this->purchaseRequestRepository->create($dto);

            $totalAmount = $this->createItems($purchaseRequest->id, $items);

            $purchaseRequest->update(['total_amount' => $totalAmount]);

            return $purchaseRequest->fresh(['branch', 'fromBranch', 'items.material']);
        });
    }

    /**
     * Super admin moves materials directly between two branches. This creates a
     * transfer-type request and approves it in the same transaction, so the
     * stock is moved immediately (no pending step, no payment).
     */
    public function storeTransfer(array $data): PurchaseRequest
    {
        return DB::transaction(function () use ($data) {
            $dto = PurchaseRequestDTO::fromArray([
                'branch_id' => $data['to_branch_id'],       // destination — receives the stock
                'from_branch_id' => $data['from_branch_id'], // source — sends the stock
                'request_type' => true,                      // transfer
                'status' => PurchaseRequestStatus::PENDING->value,
                'total_amount' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $purchaseRequest = $this->purchaseRequestRepository->create($dto);

            foreach ($data['items'] as $item) {
                $this->purchaseRequestItemRepository->create(PurchaseRequestItemDTO::fromArray([
                    'purchase_req_id' => $purchaseRequest->id,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => 0, // a transfer moves existing stock, no purchase cost
                    'total_price' => 0,
                ]));
            }

            // Approve immediately: moves the stock from the source branch into
            // the destination branch and records both inventory ledger legs.
            return $this->approve($purchaseRequest->fresh());
        });
    }

    public function update(PurchaseRequest $purchaseRequest, PurchaseRequestDTO $dto, ?array $items = null): PurchaseRequest
    {
        $this->authorizeManage($purchaseRequest);
        $this->ensurePending($purchaseRequest);

        return DB::transaction(function () use ($purchaseRequest, $dto, $items) {
            $updated = $this->purchaseRequestRepository->update($purchaseRequest, $dto);

            if ($items !== null) {
                PurchaseRequestItem::where('purchase_req_id', $updated->id)->delete();

                $totalAmount = $this->createItems($updated->id, $items);

                $updated->update(['total_amount' => $totalAmount]);
            }

            return $updated->fresh(['branch', 'fromBranch', 'items.material']);
        });
    }

    public function delete(PurchaseRequest $purchaseRequest): bool
    {
        $this->authorizeManage($purchaseRequest);
        $this->ensurePending($purchaseRequest);

        return DB::transaction(function () use ($purchaseRequest) {
            return $this->purchaseRequestRepository->delete($purchaseRequest);
        });
    }

    /**
     * Super admin may manage any request; an admin only the requests of the
     * branch he manages.
     */
    private function authorizeManage(PurchaseRequest $purchaseRequest): void
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        if ($user->hasRole('admin')) {
            $branchId = Branch::where('admin_id', $user->id)->value('id');

            if ($branchId !== null && $branchId === $purchaseRequest->branch_id) {
                return;
            }
        }

        throw new PurchaseRequestShowUnauthorizedException(
            __('You are not authorized to modify this purchase request.')
        );
    }

    /**
     * Creates the given items for a purchase request, computing each line total
     * (quantity × unit_price) and returning the summed request total.
     *
     * @param array<int, array{material_id: int|string, quantity: int|float|string, unit_price: int|float|string}> $items
     */
    private function createItems(int $purchaseReqId, array $items): float
    {
        $totalAmount = 0.0;

        foreach ($items as $item) {
            $lineTotal = round((float) $item['quantity'] * (float) $item['unit_price'], 2);
            $totalAmount += $lineTotal;

            $this->purchaseRequestItemRepository->create(PurchaseRequestItemDTO::fromArray([
                'purchase_req_id' => $purchaseReqId,
                'material_id' => $item['material_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $lineTotal,
            ]));
        }

        return $totalAmount;
    }

    /**
     * A request can only be edited or removed while it is still pending. Once it
     * is approved (stock moved, payment recorded) or rejected it is locked.
     */
    private function ensurePending(PurchaseRequest $purchaseRequest): void
    {
        if ($purchaseRequest->status !== PurchaseRequestStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => __('Only pending purchase requests can be modified.'),
            ]);
        }
    }

    public function approve(PurchaseRequest $purchaseRequest): PurchaseRequest
    {
        if ($purchaseRequest->status !== PurchaseRequestStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => __('Only pending purchase requests can be approved.'),
            ]);
        }

        return DB::transaction(function () use ($purchaseRequest) {
            $createdBy = auth()->id();
            $branch_id =  $purchaseRequest->branch_id;

            $admin_id = Branch::where('id', $branch_id)->value('admin_id');

            $purchaseRequest->loadMissing('items');

            if ($purchaseRequest->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => __('The purchase request has no items to approve.'),
                ]);
            }

            // request_type: false = purchase (bought externally), true = transfer between branches.
            $isTransfer = (bool) $purchaseRequest->request_type;

            foreach ($purchaseRequest->items as $item) {
                if ($isTransfer) {
                    // Move stock out of the supplying branch into the requesting branch.
                    $this->inventoryTransactionService->store([
                        'type' => InventoryTransactionType::TRANSFER_OUT->value,
                        'branch_id' => $purchaseRequest->from_branch_id,
                        'destination_branch_id' => $purchaseRequest->branch_id,
                        'material_id' => $item->material_id,
                        'quantity' => (float) $item->quantity,
                        'note' => "Transfer for purchase request #{$purchaseRequest->id}",
                    ], $createdBy);
                } else {
                    // Purchased externally: add the bought quantity to the requesting branch.
                    $this->inventoryTransactionService->store([
                        'type' => InventoryTransactionType::IN->value,
                        'branch_id' => $purchaseRequest->branch_id,
                        'material_id' => $item->material_id,
                        'quantity' => (float) $item->quantity,
                        'note' => "Purchase request #{$purchaseRequest->id} approved",
                    ], $createdBy);

                    Inventory::where('branch_id', $purchaseRequest->branch_id)
                        ->where('material_id', $item->material_id)
                        ->increment('quantity', (float) $item->quantity);
                }
            }

            // A purchase spends money; a transfer only moves existing stock.
            if (! $isTransfer) {
                PurchasePayment::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'branch_id' => $purchaseRequest->branch_id,
                    'paid_by' => $admin_id,
                    'amount' => $purchaseRequest->total_amount,
                    'note' => "Payment for approved purchase request #{$purchaseRequest->id}",
                ]);
            }

            $dto = PurchaseRequestDTO::fromArray([
                'status' => PurchaseRequestStatus::APPROVED->value,
                'approved_at' => now()->toDateTimeString(),
            ]);

            return $this->purchaseRequestRepository->update($purchaseRequest, $dto);
        });
    }

    public function reject(PurchaseRequest $purchaseRequest, ?string $reason): PurchaseRequest
    {

        if ($purchaseRequest->status !== PurchaseRequestStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => __('Only pending purchase requests can be rejected.'),
            ]);
        }
        return DB::transaction(function () use ($purchaseRequest, $reason) {
            $dto = PurchaseRequestDTO::fromArray([
                'status' => PurchaseRequestStatus::REJECTED->value,
                'rejection_reason' => $reason,
            ]);

            return $this->purchaseRequestRepository->update($purchaseRequest, $dto);
        });
    }
}
