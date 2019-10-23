<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterDecimalColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::table('items', function (Blueprint $table) {
            $table->decimal('sa_dm')->nullable()->change();
            $table->decimal('weight')->nullable()->change();
            $table->decimal('price')->default(0)->change();
        });

        Schema::table('item_units', function (Blueprint $table) {
            $table->decimal('rate', 10, 5)->default(1)->change();
        });

        Schema::table('item_stocks', function (Blueprint $table) {
            $table->decimal('total', 24, 4)->default(0)->change();
        });

        Schema::table('item_stockables', function (Blueprint $table) {
            $table->decimal('unit_amount',24,4)->default(0)->change();
        });

        Schema::table('request_order_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
            $table->decimal('price', 22, 2)->change();
        });

        Schema::table('incoming_good_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
        });

        Schema::table('outgoing_good_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
        });

        Schema::table('outgoing_good_verifications', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(0)->change();
            $table->decimal('target', 10, 2)->default(0)->change();
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('ngratio', 10, 2)->default(0)->change();
            $table->decimal('amount_process', 24, 4)->default(0)->change();
            $table->decimal('amount_packing', 24, 4)->default(0)->change();
        });

        Schema::table('work_order_item_lines', function (Blueprint $table) {
            $table->decimal('amount_line', 24, 4)->default(0)->change();
        });

        Schema::table('work_production_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
        });

        Schema::table('packing_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
            $table->decimal('amount_faulty', 24, 4)->default(0)->change();
        });

        Schema::table('packing_item_faults', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
        });

        Schema::table('pre_delivery_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
            $table->decimal('amount_verification', 24, 4)->default(0)->change();
        });

        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
        });

        Schema::table('forecast_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('quantity', 10, 2)->change();
            $table->decimal('price', 22, 2)->change();
        });

        Schema::table('opname_stock_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 10, 5)->default(1)->change();
            $table->decimal('init_amount', 24, 4)->change();
            $table->decimal('final_amount', 24, 4)->change();
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
