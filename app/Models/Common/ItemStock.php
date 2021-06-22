<?php
Namespace App\Models\Common;

use App\Models\Model;

class ItemStock extends Model
{
    static $stockists = [
        'FM' => 'Fresh',
        'WIP' => 'Work In Process',
        'PFG' => 'Pre-Finish Good',
        'FG' => 'Finish Good',
        'NC' => 'No-Common',
        'NCR' => 'No-Common Return',
        'NG' => 'No-Good',
        'VDO' => 'VERIFY amounable',
        'PDO.REG' => 'REGULER-PDO amounable',
        'PDO.RET' => 'RETURN-PDO amounable',
    ];

    protected $fillable = ['item_id', 'stockist', 'total'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'total' => 'double'
    ];

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public static function getStockists() {
        return collect(static::$stockists);
    }

    public static function getValidStockist($code) {
        $enum = static::getStockists();
        if(!$enum->has($code)) {
            abort(500, 'CODE STOCK INVALID!');
        }
        return $code;
    }

    // RE-CALCULATE transfer stock amount Delivery.
    public static function deliveryTransferAmount() {
        \DB::beginTransaction();
            $RESET = \DB::table('item_stocks')->whereIn('stockist', ['PDO.REG', 'PDO.RET', 'VDO'])->update(['total' => 0]);
            // $RESET = \DB::table('item_stockables')->whereIn('base_type', [
            //     'App\\Models\\Income\\PredeliveryItem',
            //     'App\\Models\\Warehouse\\OutgoingGoodVerification'
            // ])->delete();

            $pre_delivery_items = \App\Models\Income\PreDeliveryItem::all();
            $PDO = $pre_delivery_items->count();
            $pre_delivery_items->map(function($detail) {
                $detail->stockable()->delete();

                $stockist = $detail->pre_delivery->transaction == "RETURN"
                    ? "PDO.RET" : "PDO.REG" ;
                if ($detail->pre_delivery->status == "CLOSED") {
                    $detail->item->transfer($detail, $detail->amount_verification, $stockist);
                }
                else {
                    $detail->item->transfer($detail, $detail->unit_amount, $stockist);
                }
            });

            $verifications = \App\Models\Warehouse\OutgoingGoodVerification::all();
            $VERIFY = $verifications->count();
            $verifications->map(function($detail) {
                $detail->stockable()->delete();
                $detail->item->transfer($detail, $detail->unit_amount, "VDO");
            });


            $deliveries = \App\Models\Income\DeliveryOrderItem::all();
            $DELIVERY = $deliveries->count();
            $deliveries->map(function($detail) {
                $detail->stockable()->whereNotIN('stockist', ['FG'])->delete();
                if (!$detail->delivery_order) dd($detail, $detail->stockable);
                $stockist = $detail->delivery_order->transaction == "RETURN"
                    ? "PDO.RET" : "PDO.REG" ;

                $detail->item->transfer($detail, $detail->unit_amount, null, "VDO");
                $detail->item->transfer($detail, $detail->unit_amount, null, $stockist);
            });

        \DB::commit();
        return "OK, [Reset:$RESET][PDO:$PDO][VERIFY:$VERIFY][DELIVERY:$DELIVERY]";
    }

    // CHECK transfer stock amount Delivery.
    public static function deliveryCheckAmount() {
        $result = [];
        $sto =  \App\Models\Common\ItemStock::all();
        for ($i=0; $i < $sto->count(); $i++) {
            $item = ($sto[$i]);
            if(empty($result[$item['item_id']])) {
                $result[$item['item_id']] = [];
                $result[$item['item_id']]['ITEM'] = $item->item;
                $result[$item['item_id']]['I-PDO'] = (double) \App\Models\Income\PreDeliveryItem::where('item_id', $item['item_id'])->get()->sum('unit_amount');
                $result[$item['item_id']]['I-VDO'] = (double) \App\Models\Warehouse\OutgoingGoodVerification::where('item_id', $item['item_id'])->get()->sum('unit_amount');
            }
            $result[$item['item_id']][$item['stockist']] = $item['total'];
        }

        return collect(array_values($result))
        ->map(function($row) {
            $result = [];
            // $result['TEXT'] = doubleval($row['I-PDO'] ?? 0) ."<>". doubleval($row['I-VDO'] ?? 0);

            $result['STO'] = doubleval($row['PDO.REG'] ?? 0) + doubleval($row['PDO.RET'] ?? 0) - doubleval($row['VDO'] ?? 0);
            $result['ROW'] = doubleval($row['I-PDO'] ?? 0)  - doubleval($row['I-VDO'] ?? 0);
            $result['ERROR'] = round($result['STO']) != round($result['ROW']);

            return $result;
        })
        ->filter(function($row) {
            return $row['ERROR'];
        });
    }
}
