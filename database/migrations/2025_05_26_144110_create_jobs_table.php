<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('worker_id')->constrained('users');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])
                ->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jobs');
    }
};
