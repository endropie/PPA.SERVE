<?php

use App\Models\Income\RequestOrderItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAmountDeliveryOnRequestOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_order_items', function (Blueprint $table) {
            $table->decimal('amount_delivery',24,4)->default(0)->after('price');
        });

        $details = RequestOrderItem::all();
        foreach ($details as $detail) {
            $detail->amount_delivery = (double) $detail->delivery_order_items->sum('unit_amount');
            $detail->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('request_order_items', function (Blueprint $table) {
            $table->dropColumn('amount_delivery');
        });
    }
}
