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
            $table->integer('unit_id');

            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('target', 10, 2)->default(0);
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('ngratio', 10, 2)->default(0);
            $table->decimal('amount_process', 24, 4)->default(0);
            $table->decimal('amount_packing', 24, 4)->default(0);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_order_item_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_order_item_id')->unsigned();

            $table->integer('line_id')->unsigned();
            $table->boolean('ismain')->nullable();
            $table->decimal('amount_line', 24, 4)->default(0);
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
        Schema::dropIfExists('work_order_item_lines');
    }
}
