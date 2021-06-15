<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packing_item_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('packing_item_id');
            $table->bigInteger('work_order_item_id');
            $table->decimal('quantity', 20,4)->default(0);

            $table->softDeletes();
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
        Schema::dropIfExists('packing_item_orders');
    }
}
