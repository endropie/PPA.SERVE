<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkProductionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_productions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->integer('line_id')->unsigned();
            $table->date('date');
            $table->integer('shift_id')->unsigned();
            $table->enum('worktime', ['REGULER', 'OVERTIME'])->default('REGULER');
            $table->integer('operator_id')->unsigned()->nullable();

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('revise_id')->unsigned()->nullable();
            $table->string('revise_number')->nullable();

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_production_items', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('work_production_id');
            $table->integer('item_id');
            $table->integer('unit_id');
            $table->string('stockist')->nullable();
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);

            $table->integer('work_order_item_line_id')->nullable();
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
        Schema::dropIfExists('work_productions');
        Schema::dropIfExists('work_production_items');
    }
}
