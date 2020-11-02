<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_checkouts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->text('description')->nullable();
            $table->foreignId('vehicle_id');
            $table->foreignId('created_by');
            $table->timestamps();
        });

        Schema::table('delivery_loads', function (Blueprint $table) {
            $table->foreignId('delivery_checkout_id')->nullable()->after('vehicle_id');
            $table->foreign('delivery_checkout_id')
                    ->references('id')->on('delivery_checkouts')
                    ->onDelete('SET NULL')
                    ->key('delivery_loads_delivery_checkout_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_loads', function (Blueprint $table) {
            $table->dropForeign('delivery_loads_delivery_checkout_id_foreign');
            $table->dropColumn('delivery_checkout_id');
        });

        Schema::dropIfExists('delivery_checkouts');
    }
}
