<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeportationGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deportation_goods', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->date('date');
            $table->foreignId('customer_id');
            $table->text('description')->nullable();

            $table->foreignId('revise_id')->nullable();
            $table->string('revise_number')->nullable();
            $table->string('status')->default('OPEN');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('validated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deportation_good_items', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('deportation_good_id');

            $table->foreignId('item_id');
            $table->foreignId('unit_id');
            $table->decimal('unit_rate', 10, 5)->default(1);
            $table->decimal('quantity', 10, 2);
            $table->string('stockist_from');

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
        Schema::dropIfExists('deportation_goods');
        Schema::dropIfExists('deportation_good_items');
    }
}
