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
        Schema::create('user_push_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('push_token'); // Text field to handle long Expo tokens
            $table->enum('platform', ['android', 'ios', 'web'])->default('android');
            $table->boolean('active')->default(true); // To deactivate old tokens
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Unique constraint: one active token per user
            $table->unique(['user_id', 'active'], 'unique_active_user_token');
            
            // Index for faster lookups
            $table->index(['user_id', 'platform']);
            $table->index(['platform', 'active']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_push_tokens');
    }
};
