<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('address')->nullable();
            $table->string('subdistrict')->nullable();
            $table->string('district')->nullable();
            $table->integer('province_id')->nullable();
            $table->string('zipcode')->nullable();

            $table->string('npwp')->nullable();
            $table->string('pkp')->nullable();
           $table->string('bank_account')->nullable();

            $table->boolean('with_tax')->default(0);
            $table->boolean('with_pph')->default(0);
            $table->float('tax')->default(0);
            $table->float('pph_service')->default(0);
            $table->float('pph_material')->default(0);

            $table->string('invoice_mode')->nullable();
            $table->string('delivery_mode')->nullable();
            $table->string('order_mode')->nullable();

            $table->text('description')->nullable();
            $table->boolean('enable')->default(1);
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
        Schema::dropIfExists('customers');
    }
}
