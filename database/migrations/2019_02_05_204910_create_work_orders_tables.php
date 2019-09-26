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
            $table->date('date');
            $table->integer('shift_id');
            $table->string('stockist_from', 5);

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('revise_id')->unsigned()->nullable();
            $table->string('revise_number')->nullable();

            $table->integer('processed_by')->nullable();
            $table->dateTime('processed_at')->nullable();

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_order_id');

            $table->integer('item_id');
            $table->float('quantity')->default(0);
            $table->float('target')->default(0);
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('ngratio')->default(0);
            $table->float('process')->default(0);
            $table->float('amount_process')->default(0);
            $table->float('amount_packing')->default(0);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_order_item_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_order_item_id')->unsigned();

            $table->integer('line_id')->unsigned();
            $table->integer('shift_id')->unsigned()->nullable();
            $table->boolean('ismain')->nullable();
            $table->float('amount_line')->default(0);
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
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('work_order_items');
    }
}
