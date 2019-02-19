<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->integer('customer_id');

            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('work_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_order_id');

            $table->integer('item_id');
            $table->integer('quantity');
            $table->integer('ngratio')->default(0);

            $table->integer('line_id');
            $table->integer('shift_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

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
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('work_order_items');
    }
}
