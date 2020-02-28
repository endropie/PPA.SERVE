<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackingsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('packings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->integer('customer_id');
            $table->date('date');
            $table->integer('shift_id');
            $table->enum('worktime', ['REGULER', 'OVERTIME'])->default('REGULER');

            $table->integer('operator_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('packing_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('packing_id');
            $table->integer('item_id');
            $table->integer('unit_id');
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);
            $table->decimal('amount_faulty', 24, 4)->default(0);
            $table->integer('type_fault_id')->nullable();
            $table->integer('work_order_item_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('packing_item_faults', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('packing_item_id');

            $table->integer('fault_id');
            $table->decimal('quantity', 10, 2);

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
        Schema::dropIfExists('packings');
        Schema::dropIfExists('packing_items');
        Schema::dropIfExists('packing_item_faults');
    }
}
