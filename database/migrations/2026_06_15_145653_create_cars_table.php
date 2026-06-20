<?php

use App\Enums\CarEnums\FuelType;
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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('brand_id')
                ->constrained('car_brands')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('car_type_id')
                ->constrained('car_types')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('plate_number')
                ->unique();

            $table->string('brand');
            $table->string('model');

            $table->year('year');

            $table->string('color');

            $table->enum('fuel_type',FuelType::values());

            $table->unsignedTinyInteger('cylinders')
                ->nullable();

            $table->unsignedInteger('mileage')
                ->default(0);

            $table->string('image_url')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
