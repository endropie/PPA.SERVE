<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexedNumberIncomingGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoming_goods', function (Blueprint $table) {
            $table->string('indexed_number')->nullable()->after('number');
        });

        setting()->set([
            'incoming_good.indexed_number_interval'   => '{Y-m}',
            'incoming_good.indexed_number_digit'      => '3',
        ]);

        setting()->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('incoming_goods', function (Blueprint $table) {
            $table->dropColumn('indexed_number');
        });
    }
}
