<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');

            $table->string('code')->unique();
            $table->integer('customer_id');
            $table->integer('brand_id');
            $table->integer('specification_id');

            $table->string('part_name')->nullable();
            $table->string('part_alias')->nullable();
            $table->string('part_number')->nullable();

            $table->integer('number_hanger')->nullable()->default(0);
            $table->integer('packing_duration')->nullable()->default(0);
            $table->float('sa_area')->nullable()->default(0);
            $table->float('weight',20,2)->nullable()->default(0);
            
            $table->float('price', 20, 2)->default(0);

            $table->integer('category_item_id')->nullable();
            $table->integer('type_item_id')->nullable();
            $table->integer('size_id')->nullable();
            $table->integer('unit_id')->nullable();

            $table->text('description')->nullable();
            $table->boolean('enable')->default(1);
            $table->timestamps();
        });

        Schema::create('item_prelines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('line_id');
            $table->string('note')->nullable();

            $table->integer('item_id');  // =>> the field "belongsTo" relation with "Items" table.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
        Schema::dropIfExists('item_prelines');
    }
}
