<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinishedGoodsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finished_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->date('income_date');
            $table->time('income_time');

            $table->integer('customer_id');
            
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('finished_good_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('finished_good_id');

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
        Schema::dropIfExists('finished_goods');
        Schema::dropIfExists('finished_good_items');
    }
}
