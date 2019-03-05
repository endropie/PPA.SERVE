<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackingItemsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packing_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->integer('customer_id');
            $table->date('date');
            $table->time('time');
            $table->integer('shift_id');

            $table->integer('type_worktime_id');
            $table->integer('work_order_id');
            $table->integer('operator_id');

            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate');
            $table->float('quantity');
            $table->integer('type_fault_id');

            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('packing_item_faults', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('packing_item_id');

            $table->integer('fault_id');
            $table->foat('quantity');

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
        Schema::dropIfExists('work_packings');
        Schema::dropIfExists('work_packing_faults');
    }
}
