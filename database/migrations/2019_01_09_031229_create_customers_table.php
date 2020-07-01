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
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('pph_service', 10, 2)->default(0);

            $table->string('invoice_mode')->nullable();
            $table->string('delivery_mode')->nullable();
            $table->enum('order_mode', ['PO', 'NONE', 'ACCUMULATE']);

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
