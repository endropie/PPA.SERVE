<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkinProcessesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workin_processes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->date('start_date');
            $table->time('start_time');
            $table->date('end_date');
            $table->time('end_time');

            $table->integer('customer_id');
            
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('workin_process_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('workin_process_id');

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
        Schema::dropIfExists('workin_processes');
        Schema::dropIfExists('workin_process_items');
    }
}
