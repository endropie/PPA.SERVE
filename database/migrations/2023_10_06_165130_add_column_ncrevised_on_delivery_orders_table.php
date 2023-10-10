<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnNcrevisedOnDeliveryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_orders', function(Blueprint $table) {
            $table->boolean('revise_nc')->default(0)->after('revise_number');
        });

        Schema::table('delivery_order_items', function(Blueprint $table) {
            $table->decimal('quantity_nc')->default(0)->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_orders', function(Blueprint $table) {
            $table->dropColumn('revise_nc');
        });

        Schema::table('delivery_order_items', function(Blueprint $table) {
            $table->dropColumn('quantity_nc');
        });
    }
}
