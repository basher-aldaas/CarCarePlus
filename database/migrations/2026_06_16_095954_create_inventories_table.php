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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('material_id')
                ->constrained('materials')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->decimal('quantity', 12, 2)
                ->default(0);

            $table->decimal('min_quantity', 12, 2)
                ->default(0);

            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
