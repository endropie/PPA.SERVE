<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddColumnStockistOnWorkProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('work_production_items', function (Blueprint $table) {
            $table->string('stockist')->nullable()->after('quantity');
        });

        DB::transaction(function () {
            DB::table('work_production_items as wpi')
            ->join('work_order_item_lines as woli', 'woli.id', '=', 'wpi.work_order_item_line_id')
            ->join('work_order_items as woi', 'woi.id', '=', 'woli.work_order_item_id')
            ->join('work_orders as wo', 'wo.id', '=', 'woi.work_order_id')
            ->update([ 'wpi.stockist' => DB::raw("`wo`.`stockist_from`") ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_production_items', function (Blueprint $table) {
            $table->dropColumn('stockist');
        });
    }
}
