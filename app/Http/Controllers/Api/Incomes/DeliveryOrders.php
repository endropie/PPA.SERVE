<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\DeliveryOrder as Filters;
use App\Http\Requests\Income\DeliveryOrder as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\DeliveryOrder; 
use App\Models\Income\ShipDeliveryItem;
use App\Traits\GenerateNumber;

class DeliveryOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':            
                $delivery_orders = DeliveryOrder::filter($filters)->get();    
                break;

            case 'datagrid':    
                $delivery_orders = DeliveryOrder::with(['customer','operator','vehicle'])->filterable()->get();
                $delivery_orders->each->setAppends(['is_relationship']);
                break;

            default:
                $delivery_orders = DeliveryOrder::with(['customer','operator','vehicle'])->collect();
                $delivery_orders->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($delivery_orders);
    }

    public function show($id)
    {
        
        $delivery_order = DeliveryOrder::with([
            'customer', 
            'delivery_order_items.item.item_units', 
            'delivery_order_items.unit',
        ])->findOrFail($id);

        $delivery_order->setAppends(['has_revision', 'has_relationship']);

        return response()->json($delivery_order);
    }

    public function revision(Request $request, $id)
    {
        
        $this->DATABASE::beginTransaction();

        $associate = [];
        $revision = DeliveryOrder::findOrFail($id);        
        if($revision) {
          foreach ($revision->delivery_order_items as $detail) {
            $detail->item->increase($detail->unit_amount, 'FG');
            $detail->ship_delivery_items()->delete();
            $detail->request_order_items()->delete();
          }
        }

        $revision->is_revision = true;
        $revision->save();

        // Auto generate number of revision
        if($request->number) {
            $max = (int) DeliveryOrder::where('number', $request->number)->max('numrev');
            $request->merge(['numrev'=> ($max + 1)]);
        }

        $delivery_order = DeliveryOrder::create($request->all());

        $ship_delivery_items = ShipDeliveryItem::whereHas('ship_delivery', 
          function($q) use($delivery_order) {
            $q->where('customer_id', $delivery_order->customer_id);
          }
        )->get()->filter(function($detail){
                return ($detail->unit_amount - $detail->total_delivery_order_item) > 0;
          });

        $rows = $request->delivery_order_items;
        $mounting = []; $check = [];
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create DeliveryOrder items on the Delivery order revision!
            $detail = $delivery_order->delivery_order_items()->create($row);

            // Calculate stock on after the Delivery order revision!
            $detail->item->decrease($detail->unit_amount, 'FG');

            $detail->request_order_items()->create([
                'base_type' => get_class($detail),
                'base_id' => $detail->id,
                'unit_amount' => $detail->unit_amount
            ]);

            $max_amount = $detail->unit_amount;
            foreach ($ship_delivery_items as $base) {
                if($base->item_id == $detail->item_id) {

                    if($max_amount <= 0 ) break;
                    if(!isset($mounting[$base->id])) $mounting[$base->id] = 0;

                    $available = $base->unit_amount - ($base->total_delivery_order_item + $mounting[$base->id]);
                    $quantity = $max_amount > $available ? $available : $max_amount;

                    $detail->ship_delivery_items()->create([
                        'base_type' => get_class($base),
                        'base_id' => $base->id,
                        'unit_amount' => $quantity
                    ]);

                    $mounting[$base->id] += (double) $quantity;
                    $max_amount -= $quantity;                    
                }
            }
        }

        $delivery_order->request_order_id = $request->request_order_id;
        $delivery_order->ship_delivery_id = $request->ship_delivery_id;
        $delivery_order->save();

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }
}
