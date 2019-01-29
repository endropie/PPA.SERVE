<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecificationsTable extends Migration
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

            $table->integer('color_id');
            $table->float('times_spray_white');
            $table->float('times_spray_red');

            // $table->float('thick')->nullable();
            $table->float('thick_1')->nullable();
            $table->float('thick_2')->nullable();
            $table->float('thick_3')->nullable();
            $table->float('thick_4')->nullable();
            $table->string('plate_1')->nullable();
            $table->string('plate_2')->nullable();
            $table->string('plate_3')->nullable();
            $table->string('plate_4')->nullable();

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
    }
}
