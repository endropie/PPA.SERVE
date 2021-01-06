<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddBeginUnitlTimeOnPackings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packings', function (Blueprint $table) {
            $table->dateTime('begin_datetime')->nullable()->after('date');
            $table->dateTime('until_datetime')->nullable()->after('begin_datetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('packings', function (Blueprint $table) {
            $table->dropColumn('begin_datetime');
            $table->dropColumn('until_datetime');
        });
    }
}
