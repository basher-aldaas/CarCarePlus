<?php

use App\Enums\CompanyStatus;
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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('name_ar');

            $table->string('commercial_reg')
                ->unique();
            $table->string('tax_number')
                ->unique();

            $table->text('address');

            // Approval lifecycle: pending -> approved | rejected
            $table->enum('status', CompanyStatus::values())
                ->default(CompanyStatus::PENDING->value);

            $table->boolean('is_active')
                ->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
