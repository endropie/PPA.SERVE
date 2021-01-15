<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerRutesTable extends Migration
{
    public function up()
    {
        Schema::create('rutes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('cost')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rute_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rute_id');
            $table->foreignId('customer_id');
            $table->string('code');
            $table->foreign('rute_id')->on('rutes')->references('id')->onDelete('CASCADE');
        });

        Schema::table('delivery_checkouts', function (Blueprint $table) {
            $table->foreignId('rute_id')->nullable()->after('vehicle_id');
            $table->decimal('rute_amount')->default(0)->after('rute_id');
        });
    }

    public function down()
    {
        Schema::table('delivery_checkouts', function (Blueprint $table) {
            $table->dropColumn('rute_id');
            $table->dropColumn('rute_amount');
        });

        Schema::dropIfExists('rute_customers');
        Schema::dropIfExists('rutes');
    }
}
