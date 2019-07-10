<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipDeliveriesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ship_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->enum('transaction', ['REGULER', 'RETURN']);
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();

            $table->integer('customer_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();

            $table->string('transport_number')->nullable();
            $table->integer('transport_rate')->nullable();
            $table->integer('operator_id')->nullable();

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');
            
            $table->integer('request_order_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ship_delivery_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ship_delivery_id')->nullable();

            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');

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
        Schema::dropIfExists('ship_deliveries');
        Schema::dropIfExists('ship_delivery_items');
    }
}
