<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCreatedByOnOutgoingGoodVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outgoing_good_verifications', function (Blueprint $table) {
            $table->bigInteger('created_by')->unsigned()->nullable()->after('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outgoing_good_verifications', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
}
