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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('fedapay_transaction_id')->unique();
            $table->integer('amount'); // Amount in minor currency unit (e.g., cents)
            $table->string('currency', 3); // ISO 4217 currency code
            $table->enum('status', ['pending', 'approved', 'declined', 'canceled'])->default('pending');
            $table->string('purpose')->default('subscription'); // subscription, job_application, etc.
            $table->json('metadata')->nullable();
            $table->string('fedapay_customer_id')->nullable();
            $table->string('payment_method_type')->nullable(); // mobile_money, card, etc.
            $table->string('operator')->nullable(); // Orange, MTN, Moov, Wave
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['fedapay_transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
