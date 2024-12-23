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
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean("is_deleted")->default(false);


            $table->unsignedBigInteger('categorie_type_id')->nullable();
            $table->foreign('categorie_type_id')
                    ->references('id')
                    ->on('categorie_types')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
        Schema::dropIfExists('types');
    }
};
