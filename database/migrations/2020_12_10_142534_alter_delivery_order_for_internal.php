<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDeliveryOrderForInternal extends Migration
{
    public function up()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('reconcile_id');
        });

        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->dropColumn('amount_reconcile');
            $table->dropColumn('reconcile_item_id');
        });
    }

    public function down()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('reconcile_id')->after('acc_invoice_id');
        });

        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->decimal('amount_reconcile', 24, 4)->after('quantity');
            $table->foreignId('reconcile_item_id')->after('request_order_item_id');
        });
    }
}
