<?php

use App\Models\Factory\PackingItem;
use App\Models\Factory\WorkOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackingItemOrderFix extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

        ## RESET TABLE
        DB::table('packing_item_orders')->truncate();
        ## RESET CALCULATE
        $work_order_items = WorkOrderItem::all();
        foreach ($work_order_items as $work_order_item) {
            print("[work_order_item: $work_order_item->id] CALCULATE\n");
            $work_order_item->calculate();
        }

        $packing_items = PackingItem::all();
        foreach ($packing_items as $detail)
        {
            $detail->setPackingItemOrder('RESET');
        }

        // dd('OK');
        DB::commit();
    }

}
