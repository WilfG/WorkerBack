<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create pivot table for worker categories
        Schema::create('category_user', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->primary(['category_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_user');
        Schema::dropIfExists('categories');
    }
};