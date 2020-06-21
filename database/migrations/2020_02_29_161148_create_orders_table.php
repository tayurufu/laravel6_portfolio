<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedInteger('order_state')->default(0);
            $table->timestamp('order_time')->nullable();
            $table->date('send_date')->nullable();
            $table->unsignedBigInteger('total_price')->default(0);
            $table->timestamps();

//            $table->foreign('customer_id')
//                ->references('id')
//                ->on('customers')
//                ->onDelete('cascade')
//                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
