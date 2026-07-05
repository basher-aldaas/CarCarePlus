<?php

use App\Enums\WorkshopStatus;
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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();

            // Owner account (the workshop-role user who registered it).
            // Nullable so system-seeded partner workshops can exist without an owner.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('name');

            $table->string('name_ar');


            $table->text('address');

            $table->string('city');

            $table->decimal('latitude', 10, 7)
                ->nullable();

            $table->decimal('longitude', 10, 7)
                ->nullable();

            $table->enum('status', WorkshopStatus::values())
                ->default(WorkshopStatus::PENDING->value);

            $table->decimal('rating_avg', 3, 2)
                ->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
