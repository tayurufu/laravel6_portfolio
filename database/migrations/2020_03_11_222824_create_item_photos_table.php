<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_photos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_name', 20);
            $table->string('filename', 255);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['item_name', 'filename']);

            $table->foreign('item_name')->references('name')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_photos');
    }
}
