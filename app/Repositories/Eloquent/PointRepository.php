<?php

namespace App\Repositories\Eloquent;

use App\Enums\PointsTransactionType;
use App\Models\PointsTransaction;
use App\Models\UserPoint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PointRepository
{
    public function getAll(): LengthAwarePaginator
    {
        return UserPoint::with('customer')
            ->latest('updated_at')
            ->paginate(15);
    }

    public function firstOrCreate(int $customer_id): UserPoint
    {
        return UserPoint::firstOrCreate(
            ['customer_id' => $customer_id],
            ['balance' => 0]
        );
    }

    public function getPointTransactionsById(int $customer_id): Collection
    {
        return PointsTransaction::where('customer_id', $customer_id)
            ->latest()
            ->get();
    }

    public function createTransaction(
        int $customer_id,
        PointsTransactionType $type,
        int $points,
        string $reference_type,
        int $reference_id,
        ?string $note,
    ): PointsTransaction {
        $userPoint = UserPoint::lockForUpdate()->firstOrCreate(
            ['customer_id' => $customer_id],
            ['balance' => 0]
        );

        $balanceBefore = $userPoint->balance;
        $balanceAfter = $type === PointsTransactionType::EARN
            ? $balanceBefore + $points
            : $balanceBefore - $points;

        $userPoint->update(['balance' => $balanceAfter]);

        return PointsTransaction::create([
            'customer_id' => $customer_id,
            'type' => $type,
            'points' => $points,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
            'note' => $note,
        ]);
    }
}