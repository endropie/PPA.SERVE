<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCapacityOnLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->decimal('load_capacity', 20, 2)->after('ismain')->comment("factory load capacity on shift");
        });

        Schema::table('item_prelines', function (Blueprint $table) {
            $table->decimal('load_amount', 20, 2)->after('ismain')->comment("number hanger/barel on factory load");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('lines', function (Blueprint $table) {
            $table->dropColumn('load_capacity');
        });

        Schema::table('item_prelines', function (Blueprint $table) {
            $table->dropColumn('load_amount');
        });
    }
}
