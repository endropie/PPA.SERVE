<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryVerifiesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_verifies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->text('description')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('delivery_verify_items', function (Blueprint $table) {
            $table->foreignId('delivery_verify_id')->nullable()->after('id');
        });
    }

    public function down()
    {

        Schema::table('delivery_verify_items', function (Blueprint $table) {
            $table->dropColumn('delivery_verify_id');
        });

        Schema::dropIfExists('delivery_verifies');
    }
}
