<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDeliveryManualOnCustomers extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('delivery_manual_allowed')->default(0)->after('order_manual_allowed');
        });

        Schema::table('delivery_loads', function (Blueprint $table) {
            $table->boolean('is_manual')->default(0)->after('vehicle_id');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('delivery_manual_allowed');
        });

        Schema::table('delivery_loads', function (Blueprint $table) {
            $table->dropColumn('is_manual');
        });
    }
}
