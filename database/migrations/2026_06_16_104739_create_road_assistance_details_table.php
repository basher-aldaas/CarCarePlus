<?php

use App\Enums\CarEnums\CarTypeSize;
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
        Schema::create('road_assistance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Optional: the problem may not be categorised yet (e.g. set later
            // from the AI diagnosis or by staff).
            $table->foreignId('problem_type_id')
                ->nullable()
                ->constrained('problem_types')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('car_type_size', CarTypeSize::values());

            $table->text('problem_description');

            $table->string('problem_image_url')
                ->nullable();

            $table->text('ai_diagnosis')
                ->nullable();

            $table->json('ai_chat_log')
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
        Schema::dropIfExists('road_assistance_details');
    }
};
