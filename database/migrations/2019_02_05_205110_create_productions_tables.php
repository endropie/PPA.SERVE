<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->integer('line_id');
            $table->date('date');
            $table->integer('shift_id');
            $table->enum('worktime', ['REGULER', 'OVERTIME'])->default('REGULER');

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('production_items', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('production_id');
            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');

            $table->integer('work_order_item_id')->nullable();
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
        Schema::dropIfExists('productions');
        Schema::dropIfExists('production_items');
    }
}
