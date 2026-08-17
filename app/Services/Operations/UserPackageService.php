<?php

namespace App\Services\Operations;

use App\DTOs\UserPackageDTO;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Enums\UserPackageStatus;
use App\Enums\WalletTransactionEnums\WalletTransactionReason;
use App\Models\Payment;
use App\Models\UserPackage;
use App\Repositories\Eloquent\PackageRepository;
use App\Repositories\Eloquent\UserPackageRepository;
use App\Repositories\Eloquent\WalletRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserPackageService
{
    public function __construct(
        protected UserPackageRepository $userPackageRepository,
        protected PackageRepository $packageRepository,
        protected WalletRepository $walletRepository,
    ) {
    }

    public function index(int $customer_id): Collection
    {
        return $this->userPackageRepository->getByCustomerId($customer_id);
    }

    public function show(int $id): UserPackage
    {
        return $this->userPackageRepository->findById($id);
    }

    public function store(UserPackageDTO $dto, bool $chargeWallet = false): UserPackage
    {
        return DB::transaction(function () use ($dto, $chargeWallet) {
            $package = $this->packageRepository->findById($dto->package_id);

            $dto->start_date = now()->toDateString();
            $dto->end_date = now()->addDays($package->valid_days)->toDateString();
            $dto->remaining_count = $package->services_count;
            $dto->status = UserPackageStatus::ACTIVE->value;

            $userPackage = $this->userPackageRepository->create($dto);

            if ($chargeWallet) {
                $this->walletRepository->debit(
                    userId: $userPackage->user_id,
                    reason: WalletTransactionReason::ORDER_PAYMENT,
                    amount: (float) $package->price,
                    note: __('Package purchase #:id', ['id' => $userPackage->id]),
                );

                // package_id references packages.id (the plan), matching the FK
                // and how the booking flow's PACKAGE payment records it.
                Payment::create([
                    'user_id' => $userPackage->user_id,
                    'package_id' => $package->id,
                    'payment_number' => (string) Str::uuid(),
                    'type' => PaymentType::PACKAGE,
                    'method' => PaymentMethod::WALLET,
                    'status' => PaymentStatus::PAID,
                    'amount' => $package->price,
                ]);
            }

            return $userPackage;
        });
    }

    public function update(UserPackage $userPackage, UserPackageDTO $dto): UserPackage
    {
        return DB::transaction(function () use ($userPackage, $dto) {
            return $this->userPackageRepository->update($userPackage, $dto);
        });
    }

    public function destroy(UserPackage $userPackage): bool
    {
        return DB::transaction(function () use ($userPackage) {
            return $this->userPackageRepository->delete($userPackage);
        });
    }
}
