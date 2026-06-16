<?php

use App\Enums\SchedulingConflictResolutionType;
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
        Schema::create('scheduling_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id_1')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('order_id_2')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamp('conflict_time');

            $table->enum('resolution_type', SchedulingConflictResolutionType::values());

            $table->decimal('discount_offered', 12, 2)
                ->default(0);

            $table->decimal('extra_cost', 12, 2)
                ->default(0);

            $table->timestamp('resolved_at')
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
        Schema::dropIfExists('scheduling_conflicts');
    }
};
