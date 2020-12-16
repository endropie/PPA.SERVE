<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDeliveryCheckoutIdOnDeliveryOrders extends Migration
{
    public function up()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('delivery_checkout_id')->nullable()->after('revise_number');
            $table->foreign('delivery_checkout_id')
                    ->references('id')->on('delivery_checkouts')
                    ->onDelete('SET NULL')
                    ->key('delivery_orders_delivery_checkout_id_foreign');
        });
    }

    public function down()
    {

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign('delivery_orders_delivery_checkout_id_foreign');
            $table->dropColumn('delivery_checkout_id');
        });
    }
}
