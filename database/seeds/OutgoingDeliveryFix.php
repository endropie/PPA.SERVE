<?php

use App\Models\Common\ItemStock;
use App\Models\Common\ItemStockable;
use App\Models\Income\DeliveryOrderItem;
use App\Models\Income\PreDeliveryItem;
use App\Models\Warehouse\OutgoingGoodItem;
use App\Models\Warehouse\OutgoingGoodVerification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OutgoingDeliveryFix extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

        ## CLEAR PDO & VDO.
        $clear = ItemStockable::whereIn('stockist', ['PDO.REG', 'PDO.RET', 'VDO'])->delete();

        ## RESET PDO & VDO.
        $reset = ItemStock::whereIn('stockist', ['PDO.REG', 'PDO.RET', 'VDO'])->update(['total' => 0]);

        $pre_delivery_items = PreDeliveryItem::get();
        foreach ($pre_delivery_items as $detail) {
            $detail->item->distransfer($detail);
            if (!$detail->pre_delivery) abort(501, "DETAIL($detail->id) => PDO UNDEFINED!");
            $PDO = $detail->pre_delivery->transaction == "RETURN" ? 'PDO.RET' : 'PDO.REG';
            $amount = $detail->unit_amount;

            $amount = $detail->pre_delivery->status == 'CLOSED'
                ? $detail->unit_amount - (double) $detail->amount_verification
                : $detail->unit_amount;

            $detail->item->transfer($detail, $amount, $PDO);
        }

        $outgoing_verification_items = OutgoingGoodVerification::get();
        foreach ($outgoing_verification_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->item->transfer($detail, $detail->unit_amount, 'VDO');
        }

        ## OutgoingGood Detail Re-transfer
        $outgoing_good_items = OutgoingGoodItem::get();
        foreach ($outgoing_good_items as $detail) {

            $detail->item->distransfer($detail);

            if (!$detail->outgoing_good) abort(501, "DETAIL($detail->id) => OUTGOING UNDEFINED!");
            $PDO = $detail->outgoing_good->transaction == "RETURN" ? 'PDO.RET' : 'PDO.REG';
            $detail->item->transfer($detail, $detail->unit_amount, null, $PDO);
            $detail->item->transfer($detail, $detail->unit_amount, null, 'VDO');
        }

        ## Delivery Order Detail Re-transfer
        $delivery_order_items = DeliveryOrderItem::get();
        foreach ($delivery_order_items as $detail) {

            $detail->item->distransfer($detail);

            $delivery_order = $detail->delivery_order;
            if (!$delivery_order) abort(501, "ITEM($detail->id) => DELIVERY UNDEFINED!");

            $amount = $delivery_order->is_internal
                ? $detail->unit_amount - (double) $detail->amount_reconcile
                : $detail->unit_amount;

            $detail->item->transfer($detail, $amount, null, 'FG');
        }

        ## RESET MINUS PDO & VDO.
        ItemStock::where('total', '<', 0)
            ->whereIn('stockist', ['PDO.REG', 'PDO.RET', 'VDO'])
            ->whereHas('item', function($q) {
                return $q->whereHas('customer', function($q) {
                    return $q->where('order_mode', '<>', 'PO');
                });
            })
            ->update(['total' => 0]);


        // DB::rollback();
        DB::commit();
    }

}
