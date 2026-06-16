<?php

use App\Enums\OrderMaterialStatus;
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
        Schema::create('order_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('material_id')
                ->constrained('materials')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('requested_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->decimal('quantity', 12, 2);

            $table->decimal('unit_price', 12, 2);

            $table->decimal('total_price', 12, 2);

            $table->enum('status', OrderMaterialStatus::values());

            $table->timestamp('approved_at')
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
        Schema::dropIfExists('order_materials');
    }
};
