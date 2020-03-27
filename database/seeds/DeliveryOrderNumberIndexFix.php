<?php

use App\Models\Income\DeliveryOrder;
use App\Traits\GenerateNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryOrderNumberIndexFix extends Seeder
{
    use GenerateNumber;

	public function run()
    {
        DB::beginTransaction();

        ## RESET "INDEXED NUMBER" as NULL
        DeliveryOrder::withTrashed()->update(['indexed_number' => null]);

        $deliveries = DeliveryOrder::withTrashed()->select('number', 'date')->distinct()->get();
        foreach ($deliveries as $delivery) {

            $delivery_order = DeliveryOrder::withTrashed()->where('number', $delivery->number)->first();
            $prefix_code = $delivery_order->customer->code;
            $number = $this->getNextSJDeliveryIndexedNumber($delivery_order->date, $prefix_code);
            $update = DeliveryOrder::withTrashed()->where('number', $delivery_order->number)->update(['indexed_number' => $number]);
            if ($update) {
                print("DeliveryOrder($delivery_order->id) :: [$update]\n");
            }
            else {
                print("DeliveryOrder($delivery_order->id) FAILED!\n");
            }
        }

        // DB::rollback();
        DB::commit();
    }

}
