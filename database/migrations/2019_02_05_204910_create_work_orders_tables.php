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
            $table->integer('line_id');
            $table->string('stockist_from', 5);

            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('work_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_order_id');

            $table->integer('item_id');
            $table->float('quantity')->default(0);
            $table->float('target')->default(0);
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('ngratio')->default(0)->nullable();

            $table->timestamps();
        });

        Schema::create('work_order_item_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_order_item_id');

            $table->integer('line_id');
            $table->integer('shift_id')->nullable();
            $table->date('begin_date')->nullable();
            $table->date('until_date')->nullable();

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
