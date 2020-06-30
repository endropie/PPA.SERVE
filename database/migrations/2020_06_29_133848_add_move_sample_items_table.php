<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoveSampleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('sample_moved_by')->nullable()->after('created_by');
            $table->foreignId('sample_validated_by')->nullable()->after('sample_moved_by');

            $table->timestamp('sample_moved_at')->nullable();
            $table->timestamp('sample_validated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('sample_moved_by');
            $table->dropColumn('sample_validated_by');
            $table->dropColumn('sample_moved_at');
            $table->dropColumn('sample_validated_at');
        });
    }
}
