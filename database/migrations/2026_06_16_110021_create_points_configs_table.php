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
        Schema::create('points_configs', function (Blueprint $table) {
            $table->id();
            $table->decimal('earn_per_amount', 12, 2);

            $table->decimal('redeem_value', 12, 2);

            $table->unsignedInteger('min_redeem');

            $table->unsignedInteger('expiry_days');

            $table->unsignedInteger('max_earn_per_order');

            $table->boolean('is_active')
                ->default(true);

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
        Schema::dropIfExists('points_configs');
    }
};
