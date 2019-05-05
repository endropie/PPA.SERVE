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
            $table->date('ship_date')->nullable();
            $table->time('ship_time')->nullable();
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();

            $table->integer('customer_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();

            $table->integer('rit_id')->nullable();
            $table->integer('vehicle_id')->nullable();
            $table->integer('transport_id')->nullable();
            $table->integer('operator_id')->nullable();

            $table->tinyInteger('is_revision')->default(0);
            $table->text('description')->nullable();

            $table->integer('pre_delivery_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ship_delivery_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ship_delivery_id');

            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');
            $table->integer('pre_delivery_item_id');

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
