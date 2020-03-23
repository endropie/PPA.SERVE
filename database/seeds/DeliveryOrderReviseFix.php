<?php

use App\Models\Income\DeliveryOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryOrderReviseFix extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

        $delivery_orders = DeliveryOrder::withTrashed()->where('status', 'REVISED')->orderByDesc('created_at')->get();
        foreach ($delivery_orders as $revise) {
            if ($SJ = DeliveryOrder::withTrashed()->find($revise->revise_id)) {
                $SJ->revise_id = $revise->id;
                $SJ->save();

                $revise->revise_id = null;
                $revise->save();
                print("DeliveryOrder($revise->id) :: [$SJ->id => $SJ->revise_id] ($revise->revise_id)\n");
            }
            else {
                print("DeliveryOrder($revise->id) UNDEFINED!\n");
            }
        }

        // DB::rollback();
        DB::commit();
    }

}
