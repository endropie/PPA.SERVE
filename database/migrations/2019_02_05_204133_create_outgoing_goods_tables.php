<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutgoingGoodsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->enum('transaction', ['REGULER', 'RETURN']);
            $table->date('date')->nullable();
            $table->date('due_date')->nullable();

            $table->integer('customer_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();

            $table->integer('vehicle_id')->nullable();
            $table->integer('transport_rate')->nullable();
            $table->integer('operator_id')->nullable();

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('request_order_id')->nullable();

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outgoing_good_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('outgoing_good_id')->nullable();

            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outgoing_good_verifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('outgoing_good_id')->nullable();

            $table->integer('item_id');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');

            $table->integer('pre_delivery_item_id');
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
        Schema::dropIfExists('outgoing_goods');
        Schema::dropIfExists('outgoing_good_items');
        Schema::dropIfExists('outgoing_good_verifications');
    }
}
