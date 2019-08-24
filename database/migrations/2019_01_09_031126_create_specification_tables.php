<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecificationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->integer('color_id')->nullable();
            $table->string('salt_white')->nullable();
            $table->string('salt_red')->nullable();

            $table->timestamps();
        });

        Schema::create('specification_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('specification_id');
            $table->integer('thick');
            $table->string('plate')->nullable();
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
        Schema::dropIfExists('specifications');
        Schema::dropIfExists('specification_details');
    }
}
