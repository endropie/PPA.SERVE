<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColumnMainIdOnWorkOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('main_id')->nullable()->after('shift_id');
        });

        Schema::table('work_production_items', function (Blueprint $table) {
            $table->foreignId('work_order_item_id')->nullable()->after('quantity');
        });

        DB::statement("
            UPDATE `work_production_items`
            INNER JOIN work_order_item_lines ON work_order_item_lines.id = work_production_items.work_order_item_line_id
            SET work_production_items.`work_order_item_id` = work_order_item_lines.`work_order_item_id`
        ");
    }

    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('main_id');
        });

        Schema::table('work_production_items', function (Blueprint $table) {
            $table->dropColumn('work_order_item_id');
        });
    }
}
