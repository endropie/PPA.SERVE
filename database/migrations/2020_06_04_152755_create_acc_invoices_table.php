<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->date('date');
            $table->enum('order_mode', ['PO', 'NONE', 'ACCUMULATE']);
            $table->enum('invoice_mode', ['JOIN', 'SEPARATE', 'DETAIL', 'SUMMARY']);
            $table->foreignId('customer_id');
            $table->string('status')->default('OPEN');
            $table->foreignId('service_invoice_id')->nullable();
            $table->bigInteger('accurate_model_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('acc_invoice_id')->nullable()->after('request_order_id');
            $table->foreign('acc_invoice_id')
                  ->references('id')->on('acc_invoices')
                  ->onDelete('set null');
        });


        Schema::table('request_orders', function (Blueprint $table) {
            $table->foreignId('acc_invoice_id')->nullable()->after('status');
            $table->foreign('acc_invoice_id')
                  ->references('id')->on('acc_invoices')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_invoices');

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('acc_invoice_id');
        });


        Schema::table('request_orders', function (Blueprint $table) {
            $table->dropColumn('acc_invoice_id');
        });
    }
}
