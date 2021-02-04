<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAutoloadOnRequestOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::table('request_order_items', function (Blueprint $table) {
            $table->boolean('is_autoload')->default(0)->after('note');
        });
    }

    public function down()
    {
        Schema::table('request_order_items', function (Blueprint $table) {
            $table->dropColumn('is_autoload');
        });
    }
}
