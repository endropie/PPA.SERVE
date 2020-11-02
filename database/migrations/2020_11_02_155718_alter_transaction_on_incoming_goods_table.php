<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTransactionOnIncomingGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE incoming_goods MODIFY COLUMN transaction ENUM('REGULER', 'RETURN', 'SAMPLE', 'INTERNAL')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE incoming_goods MODIFY COLUMN transaction ENUM('REGULER', 'RETURN', 'SAMPLE')");
    }
}
