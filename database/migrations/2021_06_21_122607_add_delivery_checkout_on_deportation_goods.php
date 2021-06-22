<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryCheckoutOnDeportationGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deportation_goods', function (Blueprint $table) {
            $table->foreignId('delivery_checkout_id')->nullable()->after('description');
            $table->foreign('delivery_checkout_id')
                    ->references('id')->on('delivery_checkouts')
                    ->onDelete('SET NULL')
                    ->key('deportation_goods_delivery_checkout_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('deportation_goods', function (Blueprint $table) {
            $table->dropColumn('delivery_checkout_id');
        });
    }
}
