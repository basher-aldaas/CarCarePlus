<?php

use App\Enums\OtpEnums\OtpChannel;
use App\Enums\OtpEnums\OtpType;
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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('code');

            $table->enum('type',OtpType::values());

            $table->enum('channel',OtpChannel::values());

            $table->boolean('is_used')
                ->default(false);

            $table->unsignedInteger('attempts')
                ->default(0);

            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
