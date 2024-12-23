<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('lastname');
            $table->string('firstname');
            $table->string('birthday');
            $table->string('type');
            $table->string('isActive')->nullable();
            $table->string('slug')->nullable();
            $table->string('post')->nullable();
            $table->string('genre')->nullable();
            $table->string('image')->nullable();
            $table->string('number')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->boolean('email_verified')->nullable();
            $table->boolean('number_verified')->nullable();
            $table->string('password');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
