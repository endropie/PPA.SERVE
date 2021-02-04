<?php

use App\Models\Income\DeliveryOrderItem;
use Illuminate\Database\Seeder;

class DeliveryOrderInternalFixed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $delivery_order_items = DeliveryOrderItem::whereHas('delivery_order', function($q) {
            return $q->where('is_internal', 1);
        })->get();

        print("ITEMS (". $delivery_order_items->count() .")\n");

        foreach ($delivery_order_items as $detail) {
            print("DETAIL (". $detail->id ." => ". $detail->unit_amount .")\n");
            $detail->item->distransfer($detail);
            $detail->item->transfer($detail, $detail->unit_amount, null, "FG");
        }
    }
}
