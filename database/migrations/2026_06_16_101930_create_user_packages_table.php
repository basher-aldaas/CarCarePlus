<?php

use App\Enums\UserPackageStatus;
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
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('package_id')
                ->constrained('packages')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->date('start_date');

            $table->date('end_date');

            $table->unsignedInteger('remaining_count');

            $table->enum('status', UserPackageStatus::values())
                ->default('active');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_packages');
    }
};
