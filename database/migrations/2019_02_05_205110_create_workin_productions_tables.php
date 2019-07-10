<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkinProductionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workin_productions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->integer('line_id');
            $table->date('date');
            $table->integer('shift_id');
            $table->enum('worktime', ['REGULER', 'OVERTIME'])->default('REGULER');

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->timestamps();
        });

        Schema::create('workin_production_items', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('workin_production_id');
            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');

            $table->integer('work_order_item_id')->nullable();
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
        Schema::dropIfExists('workin_productions');
        Schema::dropIfExists('workin_production_items');
    }
}
