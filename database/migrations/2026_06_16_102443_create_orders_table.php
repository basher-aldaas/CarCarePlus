<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('car_id')
                ->constrained('cars')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('type', OrderType::values());

            $table->boolean('booking_type');

            $table->boolean('is_vip')
                ->default(false);

            $table->timestamp('scheduled_at')
                ->nullable();
            $table->timestamp('started_at')
                ->nullable();
            $table->timestamp('completed_at')
                ->nullable();
            $table->timestamp('cancelled_at')
                ->nullable();

            $table->text('cancel_reason')
                ->nullable();

            $table->decimal('location_lat', 10, 7)
                ->nullable();
            $table->decimal('location_lng', 10, 7)
                ->nullable();

            $table->text('location_address')
                ->nullable();

            $table->decimal('distance_km', 10, 2)
                ->nullable();

            $table->decimal('base_price', 10, 2)
                ->default(0);
            $table->decimal('vip_price', 10, 2)
                ->nullable();
            $table->decimal('distance_price', 10, 2)
                ->nullable();
            $table->decimal('sub_services_price', 10, 2)
                ->nullable();
            $table->decimal('order_material_price', 10, 2)
                ->nullable();

            $table->decimal('discount_amount', 10, 2)
                ->default(0);

            $table->decimal('total_price', 10, 2)
                ->default(0);

            $table->text('notes')
                ->nullable();

            $table->enum('status', OrderStatus::values());

            $table->timestamp('assigned_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
