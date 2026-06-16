<?php

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('package_id')
                ->nullable()
                ->constrained('packages')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('cash_confirmed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('payment_number')->unique();

            $table->enum('type', PaymentType::values());

            $table->enum('method', PaymentMethod::values());

            $table->enum('status', PaymentStatus::values());

            $table->decimal('amount', 12, 2);

            $table->unsignedInteger('points_used')
                ->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
