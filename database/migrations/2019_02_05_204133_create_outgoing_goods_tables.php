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

            $table->unsignedInteger('customer_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();

            $table->unsignedInteger('vehicle_id')->nullable();
            $table->tinyInteger('transport_rate')->nullable();
            $table->unsignedInteger('operator_id')->nullable();

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->unsignedInteger('request_order_id')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outgoing_good_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('outgoing_good_id')->nullable();

            $table->unsignedInteger('item_id');
            $table->unsignedInteger('unit_id');
            $table->float('unit_rate')->default(1);
            $table->float('quantity');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outgoing_good_verifications', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->integer('item_id')->unsigned();
            $table->integer('unit_id')->unsigned();
            $table->float('unit_rate')->default(1);
            $table->float('quantity');

            $table->integer('pre_delivery_item_id')->unsigned();

            $table->dateTime('validated_at')->nullable();
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
