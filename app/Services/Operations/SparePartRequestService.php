<?php

namespace App\Services\Operations;

use App\DTOs\SparePartRequestDTO;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\SparePartRequestStatus;
use App\Events\Operations\SparePartRequestCreated;
use App\Events\Operations\SparePartRequestDecided;
use App\Exceptions\SparePartAlreadyDecided;
use App\Exceptions\SparePartRequestOrderNotOpenException;
use App\Exceptions\SparePartRequestShowUnauthorizedException;
use App\Models\Branch;
use App\Models\Order;
use App\Models\SparePartRequest;
use App\Models\User;
use App\Repositories\Eloquent\SparePartRequestRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SparePartRequestService
{
    public function __construct(protected SparePartRequestRepository $sparePartRequestRepository)
    {}

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

        return DB::transaction(function () use ($sparePartRequest, $status, $notes) {
            $dto = SparePartRequestDTO::fromArray([
                'status' => $status->value,
                'notes' => $notes,
                'decided_at' => now()->toDateTimeString(),
            ]);

            $updated = $this->sparePartRequestRepository->update($sparePartRequest, $dto);

            SparePartRequestDecided::dispatch($updated);

            return $updated;
        });
    }
}
