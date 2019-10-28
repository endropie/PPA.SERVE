<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleBoardCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_board_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('schedule_board_id');
            $table->integer('customer_id');
            $table->timestamps();
        });

        Schema::table('schedule_boards', function (Blueprint $table) {
            $table->dropColumn('destination');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_board_customers');


        Schema::table('schedule_boards', function (Blueprint $table) {
            $table->string('destination');
        });
    }
}
