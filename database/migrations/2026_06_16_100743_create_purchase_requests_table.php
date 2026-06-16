<?php

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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('from_branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('status',\App\Enums\PurchaseRequestStatus::values())
                ->default('pending');

            $table->decimal('total_amount', 12, 2);

            $table->text('notes')
                ->nullable();

            $table->boolean('request_type')
                ->default(0); // 0 purchase / 1 transfer

            $table->text('rejection_reason')
                ->nullable();

            $table->timestamp('approved_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
