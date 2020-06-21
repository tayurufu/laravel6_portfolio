<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_name', 20)->unique();
            $table->unsignedBigInteger('location_id');
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();

            $table->foreign('item_name')
                ->references('name')
                ->on('items')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('location_id')
                ->references('id')
                ->on('stock_locations')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}
