<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryItemPricesTable extends Migration
{
    public function up()
    {
        Schema::create('category_item_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 24, 4)->default(0);
            $table->foreignId('customer_id');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('category_item_price_id')->nullable()->after('unit_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('invoice_category_price')->default(0)->after('invoice_mode');
        });

        Schema::table('acc_invoices', function (Blueprint $table) {
            $table->boolean('is_category_price')->default(0)->after('accurate_model_id');
        });
    }

    public function down()
    {
        DB::beginTransaction();

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('category_item_price_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('invoice_category_price');
        });

        Schema::table('acc_invoices', function (Blueprint $table) {
            $table->dropColumn('is_category_price');
        });

        Schema::dropIfExists('category_item_prices');

        DB::commit();
    }
}
