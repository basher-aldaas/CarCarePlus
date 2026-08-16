<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Firebase Cloud Messaging device tokens. A user may have several (phone,
     * tablet, web) so push notifications can reach every device they use.
     */
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // The FCM registration token. Unique so re-registering the same
            // device updates the existing row instead of duplicating it.
            $table->string('token')->unique();

            // 'android' | 'ios' | 'web' — free-form, filled by the client.
            $table->string('platform')->nullable();

            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};