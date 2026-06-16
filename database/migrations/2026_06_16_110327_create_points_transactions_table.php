<?php

use App\Enums\PointsTransactionType;
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
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('type', PointsTransactionType::values());

            $table->unsignedInteger('points');

            $table->unsignedInteger('balance_before');

            $table->unsignedInteger('balance_after');

            $table->string('reference_type');

            $table->unsignedBigInteger('reference_id');

            $table->timestamp('expires_at')
                ->nullable();

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
        Schema::dropIfExists('points_transactions');
    }
};
