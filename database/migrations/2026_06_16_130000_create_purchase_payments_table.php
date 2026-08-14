<?php

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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();

            //طلب الشراء الذي تمت الموافقة عليه
            $table->foreignId('purchase_request_id')
                ->constrained('purchase_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            //الفرع الذي دفع/استلم المواد
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            //المستخدم (السوبر أدمن) الذي وافق ودفع
            $table->foreignId('paid_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->decimal('amount', 12, 2);

            $table->text('note')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};