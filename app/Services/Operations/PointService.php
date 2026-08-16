<?php

namespace App\Services\Operations;

use App\DTOs\AdjustPointsDTO;
use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserPoint;
use App\Notifications\Operations\PointsAdjustedNotification;
use App\Repositories\Eloquent\PointRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PointService
{
    public function __construct(protected PointRepository $pointRepository)
    {}

    /**
     * List all customers' points balances
     */
    public function index(): LengthAwarePaginator
    {
        return $this->pointRepository->getAll();
    }

    public function getPointById(int $customer_id): UserPoint
    {
        return $this->pointRepository->firstOrCreate($customer_id);
    }

    public function getPointTransactionsById(int $customer_id): Collection
    {
        return $this->pointRepository->getPointTransactionsById($customer_id);
    }

    /**
     * Add or deduct points for a customer, recording the transaction
     */
    public function adjustPoints(AdjustPointsDTO $dto): PointsTransaction
    {
        $transaction = DB::transaction(function () use ($dto) {
            return $this->pointRepository->createTransaction(
                customer_id: $dto->customer_id,
                type: $dto->type,
                points: $dto->points,
                reference_type: 'manual_adjustment',
                reference_id: auth()->id(),
                note: $dto->note,
            );
        });

        // Let the customer know their points balance changed.
        User::find($dto->customer_id)?->notify(
            new PointsAdjustedNotification($transaction, $dto->note)
        );

        return $transaction;
    }
}
