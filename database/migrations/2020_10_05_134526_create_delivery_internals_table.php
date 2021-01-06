<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryInternalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_internals', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->date('date');
            $table->foreignId('customer_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('OPEN');
            $table->string('revised_number')->nullable();
            $table->foreignId('reason_id')->nullable();
            $table->string('reason_description')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_internal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_internal_id');
            $table->foreignId('item_id');
            $table->string('name');
            $table->string('subname');
            $table->decimal('quantity');
            $table->foreignId('unit_id');
            $table->text('note')->nullable();
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
        Schema::dropIfExists('delivery_internals');
    }
}
