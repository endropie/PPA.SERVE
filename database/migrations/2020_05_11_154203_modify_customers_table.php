<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('order_manual_allowed')->default(0)->after('order_mode');
            $table->boolean('order_monthly_actived')->default(0)->after('order_manual_allowed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('order_manual_allowed');
            $table->dropColumn('order_monthly_activated');
        });
    }
}
