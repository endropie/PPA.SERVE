<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncomingGoodsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoming_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->date('date');
            $table->time('time');

            $table->integer('customer_id');
            $table->string('reference_number');
            $table->date('reference_date');
            
            $table->string('freight_tranport_number')->nullable();
            $table->date('freight_tranport_rate')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('incoming_good_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('incoming_good_id');

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
        Schema::dropIfExists('incoming_goods');
        Schema::dropIfExists('incoming_good_items');
    }
}
