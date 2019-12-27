<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsOnDelivery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outgoing_goods', function (Blueprint $table) {
            $table->string('customer_note')->nullable()->after('customer_address');
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->string('customer_note')->nullable()->after('customer_address');
        });

        Schema::table('pre_delivery_items', function (Blueprint $table) {
            $table->string('encasement')->nullable()->after('quantity');
        });

        Schema::table('outgoing_good_verifications', function (Blueprint $table) {
            $table->string('encasement')->nullable()->after('quantity');
        });

        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->string('encasement')->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('outgoing_goods', function (Blueprint $table) {
            $table->dropColumn('customer_note');
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('customer_note');
        });

        Schema::table('pre_delivery_items', function (Blueprint $table) {
            $table->dropColumn('encasement');
        });

        Schema::table('outgoing_good_verifications', function (Blueprint $table) {
            $table->dropColumn('encasement');
        });

        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->dropColumn('encasement');
        });
    }
}
