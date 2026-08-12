<?php

namespace App\Services\Operations;

use App\DTOs\AdjustPointsDTO;
use App\Models\PointsTransaction;
use App\Models\UserPoint;
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
        return DB::transaction(function () use ($dto) {
            return $this->pointRepository->createTransaction(
                customer_id: $dto->customer_id,
                type: $dto->type,
                points: $dto->points,
                reference_type: 'manual_adjustment',
                reference_id: auth()->id(),
                note: $dto->note,
            );
        });
    }
}
