<?php

use App\Enums\PackageType;
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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->enum('type', PackageType::values());

            $table->decimal('price', 12, 2);

            $table->decimal('discount_pct', 5, 2)
                ->default(0);

            $table->unsignedInteger('services_count');

            $table->unsignedInteger('valid_days');

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
        Schema::dropIfExists('packages');
    }
};
