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
            $table->string('address')->nullable();
            $table->string('subdistrict')->nullable();
            $table->string('district')->nullable();
            $table->integer('province_id')->nullable();

            $table->string('npwp')->nullable();
            $table->string('pkp')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('fax')->nullable();
            $table->string('bank_account')->nullable();

            $table->string('cso_name')->nullable();
            $table->string('cso_phone')->nullable();
            $table->string('ppic_name')->nullable();
            $table->string('ppic_phone')->nullable();
            $table->string('qc_name')->nullable();
            $table->string('qc_phone')->nullable();

            $table->boolean('with_tax')->default(0);
            $table->boolean('with_pph')->default(0);
            $table->integer('tax')->default(0);
            $table->integer('tax_service')->default(0);
            $table->integer('tax_material')->default(0);

            $table->integer('bill_mode')->nullable();
            $table->integer('ship_mode')->nullable();
            $table->integer('order_mode')->nullable();

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
