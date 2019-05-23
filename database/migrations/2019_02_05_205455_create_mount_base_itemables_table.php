<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMountBaseItemablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mount_base_itemables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mount_type');
            $table->integer('mount_id');
            $table->string('base_type');
            $table->integer('base_id');
            $table->float('unit_amount')->default(0);
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
        Schema::dropIfExists('mount_base_itemables');
    }
}
