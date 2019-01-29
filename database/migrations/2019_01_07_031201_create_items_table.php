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

            $table->string('code')->unique();
            $table->integer('customer_id');
            $table->integer('brand_id');
            $table->integer('specification_id');

            $table->integer('order_number')->nullable();
            $table->string('part_mtr')->nullable();
            $table->string('part_fg')->nullable();
            $table->string('part_number')->nullable();

            $table->float('times_packing')->default(0);
            $table->string('sa_area')->nullable();
            $table->float('weight',20,2)->default(0);

            $table->float('price', 20, 2)->default(0);
            $table->float('price_brl', 20, 2)->default(0);
            $table->float('price_dm', 20, 2)->default(0);

            $table->integer('marketplace_id')->nullable();
            $table->integer('ordertype_id')->nullable();
            $table->integer('size_id')->nullable();
            $table->integer('unit_id')->nullable();

            $table->text('description')->nullable();
            $table->boolean('enable')->default(0);
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
