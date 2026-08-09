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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_package_id')
                ->nullable()
                ->after('company_id')
                ->constrained('user_packages')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('package_covered_amount', 10, 2)->default(0)->after('materials_price');
            $table->decimal('cash_due_amount', 10, 2)->default(0)->after('package_covered_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_package_id');
            $table->dropColumn(['package_covered_amount', 'cash_due_amount']);
        });
    }
};