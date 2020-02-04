<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReconcileColumnsOnDeliveryOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->bigInteger('reconcile_id')->unsigned()->nullable()->after('request_order_id');
        });

        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->decimal('amount_reconcile',24,4)->default(0)->after('quantity');
            $table->bigInteger('reconcile_item_id')->unsigned()->nullable()->after('request_order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('reconcile_id');
        });

        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->dropColumn('amount_reconcile');
            $table->dropColumn('reconcile_item_id');
        });
    }
}
