<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSample extends Migration
{
    public function up()
    {

        DB::statement("ALTER TABLE incoming_goods MODIFY COLUMN transaction ENUM('REGULER', 'RETURN', 'SAMPLE')");
        DB::statement("ALTER TABLE incoming_goods MODIFY COLUMN order_mode ENUM('NONE', 'PO', 'ACCUMULATE') NULL");
        DB::statement("ALTER TABLE delivery_orders MODIFY COLUMN transaction ENUM('REGULER', 'RETURN', 'SAMPLE')");

        Schema::table('items', function (Blueprint $table) {
            $table->boolean('sample')->default(0)->after('enable');

            $table->decimal('estimate_monthly_amount')->nullable()->after('unit_id');
            $table->decimal('estimate_load_capacity')->nullable()->after('unit_id');
            $table->decimal('estimate_sadm')->nullable()->after('unit_id');
            $table->decimal('estimate_price', 20, 4)->nullable()->after('unit_id');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('sample');
            $table->dropColumn('estimate_monthly_amount');
            $table->dropColumn('estimate_load_capacity');
            $table->dropColumn('estimate_sadm');
            $table->dropColumn('estimate_price');
        });
    }
}
