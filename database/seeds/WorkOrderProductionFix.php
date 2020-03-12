<?php

use App\Models\Factory\WorkOrderItem;
use App\Models\Factory\WorkProductionItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkOrderProductionFix extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

        $work_order_items = WorkOrderItem::all();

        foreach ($work_order_items as $detail) {
            $FROM = $detail->work_order->stockist_from;
            $detail->item->distransfer($detail);
            $detail->item->transfer($detail, $detail->unit_amount, 'WO'.$FROM);

            if ($detail->work_order->has_producted) {
                $amount_process = round($detail->amount_process);
                $unit_amount = round($detail->unit_amount);
                $OVER = ($unit_amount - $amount_process);
                $detail->item->transfer($detail, $OVER, null, 'WO'.$FROM);
            }
        }

        $work_production_items = WorkProductionItem::all();
        foreach ($work_production_items as $detail) {
            $line = $detail->work_order_item_line;
            if ($line->ismain) {
                $FROM = $line->work_order_item->work_order->stockist_from;
                $detail->item->distransfer($detail);
                $detail->item->transfer($detail, $detail->unit_amount,'WIP', $FROM);
                $detail->item->transfer($detail, $detail->unit_amount, null, 'WO'.$FROM);
            }
        }

        DB::commit();
    }

}
