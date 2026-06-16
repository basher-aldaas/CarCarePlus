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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('name_ar');

            $table->string('city');
            $table->text('address');

            $table->decimal('latitude', 10, 7)
                ->nullable();
            $table->decimal('longitude', 10, 7)
                ->nullable();

            $table->string('phone');

            $table->boolean('is_active')
                ->default(true);

            $table->json('working_hours')
                ->nullable();

            $table->boolean('is_24h')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
