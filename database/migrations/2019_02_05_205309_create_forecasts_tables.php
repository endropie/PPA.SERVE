<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForecastsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');

            $table->foreignId('customer_id');
            $table->foreignId('period_id');

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('forecast_items', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('forecast_id');

            $table->foreignId('item_id');
            $table->foreignId('unit_id');
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 22, 2);
            $table->string('note')->nullable();

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
        Schema::dropIfExists('forecasts');
        Schema::dropIfExists('forecast_items');
    }
}
