<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreDeliveriesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->enum('transaction', ['REGULER', 'RETURN']);

            $table->integer('customer_id');

            $table->enum('order_mode', ['PO', 'NONE', 'ACCUMULATE']);
            $table->integer('request_order_id')->nullable();

            $table->date('date');
            $table->tinyInteger('rit')->nullable();
            // $table->date('plan_begin_date');
            // $table->date('plan_until_date');

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pre_delivery_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pre_delivery_id');
            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');

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
        Schema::dropIfExists('pre_deliveries');
        Schema::dropIfExists('pre_delivery_items');
    }
}
