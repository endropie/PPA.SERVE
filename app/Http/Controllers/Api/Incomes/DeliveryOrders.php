<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\DeliveryOrder as Filters;
use App\Http\Requests\Income\DeliveryOrder as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\DeliveryOrder; 
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
                break;

            default:
                $delivery_orders = DeliveryOrder::with(['customer','operator','vehicle'])->collect();                
                break;
        }

        return response()->json($delivery_orders);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextDeliveryOrderNumber()]);

        $delivery_order = DeliveryOrder::create($request->all());

        $rows = $request->delivery_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create DeliveryOrder items on the Deliveries updated!
            $detail = $delivery_order->delivery_order_items()->create($row);

            // Calculate stock on after the Deliveries updated!
            $detail->item->decrease($detail->unit_amount, 'FG');
        }

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }

    public function show($id)
    {
        $delivery_order = DeliveryOrder::with(['delivery_order_items.item.item_units', 'delivery_order_items.unit'])->findOrFail($id);
        $delivery_order->is_editable = (!$delivery_order->is_related);

        return response()->json($delivery_order);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        $delivery_order->update($request->input());

        // Delete old DeliveryOrder items items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->delivery_order_items, 'id')));
        $delete_details = $delivery_order->delivery_order_items()->whereNotIn('id', $ids)->get();
        
        if($delete_details) {
          foreach ($delete_details as $detail) {
            // Calculate first, before deleting!
            $detail->item->increase($detail->unit_amount, 'FG');
            $detail->delete();
          }
        }

        $rows = $request->delivery_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $oldDetail = $delivery_order->delivery_order_items()->find($row['id']);
            if($oldDetail) {
                // Calculate stock on before the DeliveryOrder items updated!
                $oldDetail->item->increase($oldDetail->unit_amount, 'FG');
            }

            // Update or Create detail row
            $newDetail = $delivery_order->delivery_order_items()->updateOrCreate(['id' => $row['id']], $row);
            // Calculate stock on after the DeliveryOrder items updated!
            $newDetail->item->decrease($newDetail->unit_amount, 'FG');
        }

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);
        if($details = $delivery_order->delivery_order_items) {
            foreach ($details as $detail) {
                $detail->item->decrease($detail->unit_amount, 'FG');
            }
        }
        $delivery_order->delivery_order_items()->delete();
        $delivery_order->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
