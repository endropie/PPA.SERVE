<?php

use App\Models\Factory\WorkOrderItem;
use App\Models\Factory\WorkProductionItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkOrderItemFix extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

        $work_order_items = WorkOrderItem::all();

        foreach ($work_order_items as $detail) {
            $detail->calculate();

            if (round($detail->amount_process) > round($detail->unit_amount)) {
                print("CALCULATE[$detail->id] ($detail->amount_process > $detail->unit_amount \n");
            }
            // else print("CALCULATE[$detail->id]\n");
        }

        // DB::rollback(); print("DB::ROLLBACK\n");
        DB::commit(); print("DB::COMMIT\n");
    }

}
