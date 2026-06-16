<?php

use App\Enums\ContractStatus;
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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('workshop_id')
                ->constrained('workshops')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('title');

            $table->decimal('value', 12, 2);

            $table->date('start_date');

            $table->date('end_date');

            $table->text('terms');

            $table->string('file_url')
                ->nullable();

            $table->enum('status', ContractStatus::values());

            $table->unsignedInteger('renewal_count')
                ->default(0);

            $table->timestamp('last_renewed_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
