<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchemaDeliveryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->enum('transaction', ['REGULER', 'RETURN']);
            $table->date('date');
            $table->time('time');
            $table->tinyInteger('rit');
            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->foreignId('customer_id');
            $table->foreignId('incoming_good_id')->nullable();

            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_task_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_task_id');
            $table->foreignId('item_id');
            $table->foreignId('unit_id');
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);
            $table->string('encasement')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('delivery_verify_items', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->tinyInteger('rit');
            $table->foreignId('customer_id');
            $table->foreignId('item_id');
            $table->foreignId('unit_id');
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);
            $table->string('encasement')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamp('loaded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_loads', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->date('date');
            $table->tinyInteger('rit');
            $table->enum('transaction', ['REGULER', 'RETURN']);
            $table->enum('order_mode', ['NONE', 'PO', 'ACCUMULATE']);

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_note')->nullable();

            $table->foreignId('customer_id');
            $table->foreignId('vehicle_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_load_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_load_id');
            $table->foreignId('item_id');
            $table->foreignId('unit_id');
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);
            $table->string('encasement')->nullable();
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
        Schema::dropIfExists('delivery_tasks');
        Schema::dropIfExists('delivery_task_items');
        Schema::dropIfExists('delivery_verify_items');

        Schema::dropIfExists('delivery_loads');
        Schema::dropIfExists('delivery_load_items');
    }
}
