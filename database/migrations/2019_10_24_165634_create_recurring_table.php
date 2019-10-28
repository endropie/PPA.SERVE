<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurringTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recurring', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('recurable');
            $table->string('frequency');
            $table->boolean('custom')->default(0);
            $table->json('optional')->nullable();
            $table->integer('interval')->default(1);
            $table->date('started_at');
            $table->integer('count')->default(0);
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
        Schema::dropIfExists('recurring');
    }
}
