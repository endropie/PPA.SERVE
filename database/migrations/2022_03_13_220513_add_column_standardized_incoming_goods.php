<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStandardizedIncomingGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoming_goods', function(Blueprint $table) {
            $table->timestamp('standardized_at')->nullable()->after('created_by');
            $table->foreignId('standardized_by')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incoming_goods', function(Blueprint $table) {
            $table->dropColumn('standardized_at');
            $table->dropColumn('standardized_by');
        });
    }
}
