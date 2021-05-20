<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->tinyInteger('intday');
            $table->time('time');
            $table->timestamps();

            $table->unique(['customer_id', 'intday', 'time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_trips');
    }
}
