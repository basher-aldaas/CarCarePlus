<?php

use App\Enums\CarEnums\CarTypeSize;
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
        Schema::create('towing_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('car_type_size', CarTypeSize::values());

            $table->decimal('destination_lat', 10, 7)
                ->nullable();
            $table->decimal('destination_lng', 10, 7)
                ->nullable();

            $table->text('destination_address');

            $table->text('notes')
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
        Schema::dropIfExists('towing_details');
    }
};