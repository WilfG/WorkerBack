<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('professions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->index('category_id');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });

        // Add profession_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('profession_id')->nullable()->constrained();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->dropForeign(['profession_id']);
            $table->dropColumn('profession_id');
        });

        Schema::dropIfExists('professions');
    }
};
