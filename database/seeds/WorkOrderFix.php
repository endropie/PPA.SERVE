<?php

use App\Models\Factory\WorkOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WorkOrderFix extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

        $work_order_items = WorkOrderItem::all();

        foreach ($work_order_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->item->transfer($detail, $detail->unit_amount, 'WO');

            if ($detail->work_order->has_producted) {

                $amount_process = round($detail->amount_process);
                $unit_amount = round($detail->unit_amount);
                $OVER = ($unit_amount - $amount_process);
                $detail->item->transfer($detail, $OVER, null, 'WO');
            }
        }

        DB::commit();
    }

}
