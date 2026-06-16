<?php

use App\Enums\OrderSubServiceStatus;
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
        Schema::create('order_sub_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('sub_service_id')
                ->constrained('sub_services')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('price', 10, 2);

            $table->enum('status', OrderSubServiceStatus::values());

            $table->text('notes')
                ->nullable();

            $table->timestamp('checked_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_sub_services');
    }
};
