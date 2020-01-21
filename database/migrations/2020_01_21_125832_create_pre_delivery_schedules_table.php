<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreDeliverySchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_delivery_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('pre_delivery_id')->unsigned();
            $table->bigInteger('schedule_board_id')->unsigned();
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
        Schema::dropIfExists('pre_delivery_schedules');
    }
}
