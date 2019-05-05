<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\ShipDelivery as Filters;
use App\Http\Requests\Income\ShipDelivery as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\ShipDelivery; 
use App\Traits\GenerateNumber;

class ShipDeliveries extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':            
                $ship_deliveries = ShipDelivery::filter($filters)->get();    
                break;

            case 'datagrid':    
                $ship_deliveries = ShipDelivery::with(['customer','operator','vehicle'])->filterable()->get();
                break;

            default:
                $ship_deliveries = ShipDelivery::with(['operator','vehicle', 
                    'delivery_orders' => function($q) { $q->select(['id', 'ship_delivery_id', 'number']);},
                    'customer' => function($q) { $q->select(['id', 'name']);}
                ])->collect();                
                break;
        }

        return response()->json($ship_deliveries);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextShipDeliveryNumber()]);

        $ship_delivery = ShipDelivery::create($request->all());

        $rows = $request->ship_delivery_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create ShipDelivery items on the Deliveries updated!
            $detail = $ship_delivery->ship_delivery_items()->create($row);

            // Calculate stock on after the Deliveries updated!
            $detail->item->decrease($detail->unit_amount, 'FG');
        }

        $this->storeDeliveryOrder($ship_delivery);

        $this->DATABASE::commit();
        return response()->json($ship_delivery);
    }

    public function show($id)
    {
        $ship_delivery = ShipDelivery::with(['ship_delivery_items.item.item_units', 'ship_delivery_items.unit'])->findOrFail($id);
        $ship_delivery->is_editable = (!$ship_delivery->is_related);

        return response()->json($ship_delivery);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $ship_delivery = ShipDelivery::findOrFail($id);

        $ship_delivery->update($request->input());

        // Delete old ShipDelivery items items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->ship_delivery_items, 'id')));
        $delete_details = $ship_delivery->ship_delivery_items()->whereNotIn('id', $ids)->get();
        
        if($delete_details) {
          foreach ($delete_details as $detail) {
            $detail->delete();
          }
        }

        $rows = $request->ship_delivery_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Update or Create detail row
            $newDetail = $ship_delivery->ship_delivery_items()->updateOrCreate(['id' => $row['id']], $row);
        }

        $this->storeDeliveryOrder($ship_delivery);

        $this->DATABASE::commit();
        return response()->json($ship_delivery);
    }

    public function storeDeliveryOrder($ship_delivery) {
        $delivery_orders = $ship_delivery->delivery_orders;
        foreach ($delivery_orders as $delivery_order) {
            $delivery_order_items = $delivery_order->delivery_order_items;
            foreach ($delivery_order_items as $delivery_order_item) {

                $delivery_order_item->item->increase($delivery_order_item->unit_amount, 'FG');
                $delivery_order_item->base_request_order_items()->delete();
                $delivery_order_item->delete();
            }
            $delivery_order->delete();
        }

        $list = [];
        foreach ($ship_delivery->ship_delivery_items as $ship_delivery_item) {
            $total_amount = $ship_delivery_item->unit_amount;

            foreach ($ship_delivery_item->pre_delivery_item->base_request_order_items as $key => $item) {
                
                $unit_amount = $total_amount > $item->base->unit_amount ? $item->base->unit_amount : $total_amount;
                $total_amount -= $unit_amount;
                
                if($unit_amount <= 0.1) break;
                $list[$item->base->request_order_id][$key] = [
                    'ship_delivery_item_id' => $ship_delivery_item->id,
                    'item_id' => $ship_delivery_item->item_id,
                    'unit_id' => $ship_delivery_item->unit_id,
                    'unit_rate' => $ship_delivery_item->unit_rate,
                    'quantity' => ($unit_amount / $ship_delivery_item->unit_rate)
                ];
                $assosiate[$item->base->request_order_id][$key] = [
                    'base_type' => $item->base_type,
                    'base_id' => $item->base->id,
                ];
            }

             if($total_amount > 0.1) abort(501, 'Total unit invalid! --> '. $total_amount .'from'. $ship_delivery_item->unit_amount);
        }

        foreach ($list as $id => $rows) {
            $delivery_order = $ship_delivery->delivery_orders()->create([
                'number' => $this->getNextDeliveryOrderNumber(),
                'transaction' => $ship_delivery->transaction,
                'customer_id' => $ship_delivery->customer_id,
                'customer_name' => $ship_delivery->customer_name,
                'customer_phone' => $ship_delivery->customer_phone,
                'customer_address' => $ship_delivery->customer_address,
            ]);
            foreach ($rows as $key => $row) {
                $newDetail = $delivery_order->delivery_order_items()->create($row);
                $newDetail->item->decrease($newDetail->unit_amount, 'FG');

                $newDetail->mount_extractables()->create($assosiate[$id][$key]);
            }
        }

        // abort(501, json_encode($list));
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $ship_delivery = ShipDelivery::findOrFail($id);
        if($details = $ship_delivery->ship_delivery_items) {
            foreach ($details as $detail) {
                $detail->item->decrease($detail->unit_amount, 'FG');
            }
        }
        $ship_delivery->ship_delivery_items()->delete();
        $ship_delivery->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
