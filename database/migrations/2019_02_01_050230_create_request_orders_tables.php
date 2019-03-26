<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->date('begin_date');
            $table->date('until_date');

            $table->integer('customer_id');
            $table->string('reference_number')->nullable();
            $table->date('reference_date')->nullable();
            
            $table->enum('order_mode', ['BASEON', 'NONE', 'ACCUMULATE']);
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('request_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_order_id');

            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');
            $table->float('price');
            $table->string('note')->nullable();

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
        Schema::dropIfExists('request_orders');
        Schema::dropIfExists('request_order_items');
    }
}
