<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->integer('years_experience')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'hourly_rate',
                'years_experience'
            ]);
        });
    }
};
