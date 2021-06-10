<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForecastLoadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forecast_loads', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->foreignId('period_id');
            $table->foreignId('created_by');
            $table->timestamps();

            $table->unique(['period_id', 'number']);
        });

        Schema::create('forecast_load_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_load_id');
            $table->foreignId('item_id');
            $table->foreignId('line_id');
            $table->decimal('amount', 20, 2);
            $table->decimal('amount_load', 22, 4);
            $table->decimal('capacity', 22, 4);
            $table->timestamps();

            $table->unique(['forecast_load_id', 'item_id', 'line_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forecast_load_items');
        Schema::dropIfExists('forecast_loads');
    }
}
