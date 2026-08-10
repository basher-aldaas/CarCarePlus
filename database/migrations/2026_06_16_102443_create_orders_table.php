<?php

use App\Enums\OrderEnums\OrderStatus;
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

            $table->uuid('booking_group_id')
                ->nullable()
                ->index();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('user_package_id')
                ->nullable()
                ->constrained('user_packages')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('car_id')
                ->constrained('cars')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('workshop_id')
                ->nullable()
                ->constrained('workshops')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();



            $table->boolean('booking_type'); // 1->immediate or 0->scheduled

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


            $table->decimal('discount_amount', 10, 2)
                ->default(0);

            $table->decimal('total_price', 10, 2)
                ->default(0);

            $table->decimal('service_price', 10, 2)->default(0);
            $table->decimal('sub_service_price', 10, 2)->default(0);
            $table->decimal('materials_price', 10, 2)->default(0);

            $table->decimal('package_covered_amount', 10, 2)->default(0);
            $table->decimal('cash_due_amount', 10, 2)->default(0);



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
