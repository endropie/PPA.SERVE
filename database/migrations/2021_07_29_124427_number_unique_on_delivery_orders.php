<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NumberUniqueOnDeliveryOrders extends Migration
{
    public function up()
    {
        DB::update('update delivery_orders set revise_number = 0 where revise_number IS NULL');

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unsignedInteger('revise_number')->default(0)->nullable(false)->change();
            $table->unique(['number', 'revise_number'], 'unique_number_on_delivery_orders');
        });
    }

    public function down()
    {

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropUnique('unique_number_on_delivery_orders');
        });
    }
}
