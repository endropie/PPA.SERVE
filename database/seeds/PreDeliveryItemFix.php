<?php

use App\Models\Income\PreDeliveryItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreDeliveryItemFix extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

        $pre_delivery_items = PreDeliveryItem::all();
        foreach ($pre_delivery_items as $pre_delivery_item) {
            print("[pre_delivery_item: $pre_delivery_item->id] CALCULATE:: ");
            $pre_delivery_item->calculate();
            if (round($pre_delivery_item->unit_amount) < round($pre_delivery_item->amount_verification)) {
                print("OVER [$pre_delivery_item->unit_amount < $pre_delivery_item->amount_verification]");
            }
            print("\n");
        }

        DB::commit();
    }

}
