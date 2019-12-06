<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEstimateOnRequestOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_orders', function (Blueprint $table) {
            $table->boolean('is_estimate')->default(0)->after('order_mode');
            $table->string('estimate_number')->nullable()->after('is_estimate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_orders', function (Blueprint $table) {
            $table->dropColumn('is_estimate');
            $table->dropColumn('estimate_number');
        });
    }
}
