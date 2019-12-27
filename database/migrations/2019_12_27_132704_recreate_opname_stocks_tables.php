<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateOpnameStocksTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('opname_stocks');
        Schema::dropIfExists('opname_stock_items');

        Schema::create('opname_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number');
            $table->date('date');

            $table->integer('item_id');
            $table->string('stockist');
            $table->float('init_amount');

            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('revise_id')->nullable();
            $table->integer('revise_number')->nullable();

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('opname_stock_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('opname_stock_id');
            $table->string('reference', 10);
            $table->decimal('quantity');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);

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
        Schema::dropIfExists('opname_stocks');
        Schema::dropIfExists('opname_stock_items');
    }
}
