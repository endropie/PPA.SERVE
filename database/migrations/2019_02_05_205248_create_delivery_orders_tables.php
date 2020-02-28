<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->enum('transaction', ['REGULER', 'RETURN']);
            $table->date('date')->nullable();

            $table->unsignedInteger('customer_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_note')->nullable();

            $table->string('vehicle_id')->nullable();
            $table->tinyInteger('rit')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->unsignedInteger('revise_id')->nullable();
            $table->string('revise_number')->nullable();
            $table->unsignedInteger('outgoing_good_id')->nullable();
            $table->unsignedInteger('request_order_id')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('delivery_order_id');

            $table->unsignedInteger('item_id');
            $table->unsignedInteger('unit_id');
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);
            $table->string('encasement')->nullable();

            $table->unsignedInteger('request_order_item_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('delivery_order_items');
    }
}
