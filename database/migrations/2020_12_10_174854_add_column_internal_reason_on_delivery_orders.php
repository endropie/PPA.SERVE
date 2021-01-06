<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInternalReasonOnDeliveryOrders extends Migration
{
    public function up()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('internal_reason_id')->nullable()->after('status');
            $table->text('internal_reason_description')->nullable()->after('internal_reason_id');
        });
    }

    public function down()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('internal_reason_id');
            $table->dropColumn('internal_reason_description');
        });
    }
}
