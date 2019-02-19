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
            $table->integer('type_worktime_id');

            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('workin_production_items', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('workin_production_id');
            $table->integer('item_id');
            $table->integer('quantity');

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
