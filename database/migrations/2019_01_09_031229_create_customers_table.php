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

            $table->string('name');
            $table->string('address');
            $table->string('subdistrict');
            $table->string('district');
            $table->integer('province_id');

            $table->string('npwp');
            $table->string('pkp');
            $table->string('phone');
            $table->string('fax');
            $table->string('email');
            $table->string('pic');
            $table->string('bank_number');

            $table->string('ppic_name');
            $table->string('ppic_contact');
            $table->string('qc_name');
            $table->string('qc_contact');

            $table->boolean('with_tax');
            $table->integer('tax_service');
            $table->integer('tax_material');

            $table->integer('bill_methode');
            $table->string('shipping_documentation');
            $table->string('purchase_documentation');


            $table->text('description');
            $table->boolean('enable');
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
