<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->string('part_mtr');
            $table->string('part_fg');
            $table->string('part_number');

            $table->integer('customer_id');
            $table->integer('brand_id');
            $table->integer('spesification_id');
            $table->integer('order_number');

            $table->time('packing_time');
            $table->string('sa_area');
            $table->float('weight',20,2);

            $table->float('price', 20, 2);
            $table->float('price_brl', 20, 2);
            $table->float('price_dm', 20, 2);

            $table->integer('category_id');
            $table->integer('marketplace_id');
            $table->integer('ordertype_id');
            $table->integer('unit_id');

            $table->text('description');
            $table->boolean('enable');
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
        Schema::dropIfExists('items');
    }
}
