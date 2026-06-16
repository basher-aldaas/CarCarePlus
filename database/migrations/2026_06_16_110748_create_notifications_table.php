<?php

use App\Enums\NotificationType;
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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('title');

            $table->text('body');

            $table->enum('type', NotificationType::values());

            $table->string('reference_type')
                ->nullable();

            $table->unsignedBigInteger('reference_id')
                ->nullable();

            $table->boolean('is_read')
                ->default(false);

            $table->timestamp('read_at')
                ->nullable();

            $table->json('sent_via')
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
        Schema::dropIfExists('notifications');
    }
};
