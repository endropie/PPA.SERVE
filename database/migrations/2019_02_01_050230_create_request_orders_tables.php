<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->date('date');
            $table->date('actived_date')->nullable();
            $table->integer('customer_id')->unsigned();
            $table->string('reference_number')->nullable();

            $table->enum('transaction', ['REGULER', 'RETURN'])->default('REGULER');
            $table->enum('order_mode', ['PO', 'NONE', 'ACCUMULATE']);
            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('revise_id')->unsigned()->nullable();
            $table->integer('revise_number')->nullable();

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('request_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_order_id')->unsigned();

            $table->integer('item_id')->unsigned();
            $table->integer('unit_id')->unsigned();
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 24,4);
            $table->decimal('amount_delivery',24,4)->default(0);

            $table->string('note')->nullable();

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
        Schema::dropIfExists('request_orders');
        Schema::dropIfExists('request_order_items');
    }
}
