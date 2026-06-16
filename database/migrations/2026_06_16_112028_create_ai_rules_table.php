<?php

use App\Enums\AiRuleConditionType;
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
        Schema::create('ai_rules', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('name_ar');

            $table->enum('type', AiRuleConditionType::values());

            $table->string('condition_key')
                ->nullable();

            $table->string('condition_value')
                ->nullable();

            $table->string('car_brand')
                ->nullable();

            $table->string('car_type')
                ->nullable();

            $table->string('fuel_type')
                ->nullable();

            $table->text('response_template');

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
        Schema::dropIfExists('ai_rules');
    }
};
