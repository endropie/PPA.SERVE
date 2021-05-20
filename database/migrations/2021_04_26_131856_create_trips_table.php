<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->date('date');
            $table->time('time');
            $table->timestamps();

            $table->unique(['customer_id', 'date', 'time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
