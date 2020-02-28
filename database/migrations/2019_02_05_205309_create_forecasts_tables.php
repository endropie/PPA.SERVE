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
            $table->date('begin_date');
            $table->date('until_date');

            $table->unsignedInteger('customer_id');

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('forecast_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('forecast_id');

            $table->unsignedInteger('item_id');
            $table->unsignedInteger('unit_id');
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
