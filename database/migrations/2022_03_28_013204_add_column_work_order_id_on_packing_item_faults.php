<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnWorkOrderIdOnPackingItemFaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packing_item_faults', function(Blueprint $table) {
            $table->foreignId('work_order_item_id')->nullable()->after('fault_id');
        });

        Schema::table('work_order_items', function(Blueprint $table) {
            $table->decimal('amount_faulty', 24, 4)->default(0)->after('amount_packing');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packing_item_faults', function(Blueprint $table) {
            $table->dropColumn('work_order_item_id');
        });

        Schema::table('work_order_items', function(Blueprint $table) {
            $table->dropColumn('amount_faulty');
        });
    }
}
