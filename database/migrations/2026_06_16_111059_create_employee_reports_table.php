<?php

use App\Enums\EmployeeReportStatus;
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
        Schema::create('employee_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->text('problem_description');

            $table->json('affected_parts')
                ->nullable();

            $table->json('images')
                ->nullable();

            $table->text('recommendation')
                ->nullable();

            $table->enum('status', EmployeeReportStatus::values());

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->timestamp('created_at')
                ->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_reports');
    }
};
