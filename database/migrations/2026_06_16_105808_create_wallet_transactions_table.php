<?php

use App\Enums\WalletTransactionEnums\WalletTransactionReason;
use App\Enums\WalletTransactionEnums\WalletTransactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('type', WalletTransactionType::values());

            $table->enum('reason', WalletTransactionReason::values());

            $table->decimal('amount', 12, 2);

            $table->decimal('balance_before', 12, 2);

            $table->decimal('balance_after', 12, 2);

            $table->text('note')
                ->nullable();

            $table->timestamp('created_at')
                ->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
