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
        Schema::create('package_service_sub_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_service_id')
                ->constrained('package_services')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('sub_service_id')
                ->constrained('sub_services')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->decimal('price_override', 12, 2)
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
        Schema::dropIfExists('package_service_sub_services');
    }
};
