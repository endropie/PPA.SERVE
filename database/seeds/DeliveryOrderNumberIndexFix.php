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

        $deliveries = DeliveryOrder::where('indexed_number', 'LIKE', '%03/1000')->get();
        foreach ($deliveries as $delivery) {
            $prefix_code = $delivery->customer->code;
            $number = $this->getNextSJDeliveryIndexedNumber($delivery->date, $prefix_code);
            $update = DeliveryOrder::withTrashed()->where('number', $delivery->number)->update(['indexed_number' => $number]);
            if ($update) {
                $new = DeliveryOrder::find($delivery->id);
                print("DeliveryOrder($delivery->id) ($new->indexed_number) :: [$update]\n");
            }
            else {
                print("DeliveryOrder($delivery->id) FAILED!\n");
            }
        }

        DB::rollback();
        // DB::commit();
    }

}
