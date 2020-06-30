<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomerOrderLots extends Migration
{

    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('order_lots')->default(0)->after('order_mode');
        });

        Schema::table('incoming_good_items', function (Blueprint $table) {
            $table->string('lots')->nullable()->after('note');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('order_lots');
        });

        Schema::table('incoming_good_items', function (Blueprint $table) {
            $table->dropColumn('lots');
        });
    }
}
