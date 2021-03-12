<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryHandoversTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_handovers', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->text('description')->nullable();
            $table->foreignId('customer_id');

            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('delivery_handover_id')->nullable()->after('request_order_id');
        });
    }

    public function down()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('delivery_handover_id');
        });

        Schema::dropIfExists('delivery_handovers');
    }
}
