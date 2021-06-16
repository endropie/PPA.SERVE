<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackingLoadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packing_loads', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('status')->default('OPEN');
            $table->foreignId('created_by');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('packing_load_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_load_id');
            $table->foreignId('item_id');
            $table->foreignId('unit_id');
            $table->decimal('unit_rate')->default(1);
            $table->decimal('quantity', 20, 2);
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
        Schema::dropIfExists('packing_load_items');
        Schema::dropIfExists('packing_loads');
    }
}
