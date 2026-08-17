<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Widen the payment type enum to cover spare-part charges. MySQL keeps
        // a real ENUM; other drivers (e.g. SQLite in tests) store it as a
        // varchar with a CHECK constraint, so relax that to a plain string.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY type ENUM('order','package','wallet_topup','spare') NOT NULL");
        } else {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('type')->change();
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('spare_part_request_id')
                ->nullable()
                ->after('package_id')
                ->constrained('spare_part_requests')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['spare_part_request_id']);
            $table->dropColumn('spare_part_request_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY type ENUM('order','package','wallet_topup') NOT NULL");
        }
    }
};
