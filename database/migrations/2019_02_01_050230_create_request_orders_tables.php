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
            $table->date('date');

            $table->integer('customer_id');
            $table->string('reference_number')->nullable();

            $table->enum('transaction', ['REGULER', 'RETURN'])->default('REGULER');
            $table->enum('order_mode', ['PO', 'NONE', 'ACCUMULATE']);
            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
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

            $table->integer('outgoing_good_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
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
