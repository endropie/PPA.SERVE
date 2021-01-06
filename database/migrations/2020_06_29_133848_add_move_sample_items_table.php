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
            $table->date('estimate_begin_date')->nullable()->after('estimate_price');
            $table->jsonb('depics')->nullable()->after('description');

            $table->enum('project', ['NONE', 'NEW', 'MIGRATE'])->default('NONE')->after('sample');
            $table->string('project_number')->nullable()->after('project');

            $table->foreignId('sample_enginered_by')->nullable()->after('created_by');
            $table->foreignId('sample_validated_by')->nullable()->after('created_by');

            $table->timestamp('sample_depicted_at')->nullable();
            $table->timestamp('sample_enginered_at')->nullable();
            $table->timestamp('sample_priced_at')->nullable();
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

            $table->dropColumn('project');
            $table->dropColumn('depics');

            $table->dropColumn('sample_enginered_by');
            $table->dropColumn('sample_validated_by');

            $table->dropColumn('sample_depicted_at');
            $table->dropColumn('sample_enginered_at');
            $table->dropColumn('sample_priced_at');
            $table->dropColumn('sample_validated_at');
        });
    }
}
