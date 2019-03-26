<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveriesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->date('date');
            $table->time('time');

            $table->integer('customer_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();

            $table->integer('rit_id')->nullable();
            $table->integer('vehicle_id')->nullable();
            $table->integer('transport_id')->nullable();
            $table->integer('operator_id')->nullable();
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();

            $table->enum('transaction',['REGULER', 'RETURN'])->default('REGULER');
            $table->tinyInteger('is_revision')->default(0);
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('delivery_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_id');

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
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('delivery_items');
    }
}
