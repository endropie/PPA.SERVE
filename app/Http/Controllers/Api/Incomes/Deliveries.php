<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Income\Delivery as Request;
use App\Http\Controllers\ApiController;

use App\Models\Income\Delivery; 
use App\Traits\GenerateNumber;
use function GuzzleHttp\json_encode;

class Deliveries extends ApiController
{
    use GenerateNumber;

    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $deliveries = Delivery::filterable()->get();    
                break;

            case 'datagrid':    
                $deliveries = Delivery::with(['customer','operator','vehicle'])->filterable()->get();
                break;

            default:
                $deliveries = Delivery::with(['customer','operator','vehicle'])->collect();                
                break;
        }

        return response()->json($deliveries);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextDeliveryNumber()]);

        $delivery = Delivery::create($request->all());

        $rows = $request->delivery_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create Delivery items on the Deliveries updated!
            $detail = $delivery->delivery_items()->create($row);

            // Calculate stock on after the Deliveries updated!
            $detail->item->decrease($detail->unit_stock, 'FG');
        }

        $this->DATABASE::commit();
        return response()->json($delivery);
    }

    public function show($id)
    {
        $delivery = Delivery::with(['delivery_items.item.item_units', 'delivery_items.unit'])->findOrFail($id);
        $delivery->is_editable = (!$delivery->is_related);

        return response()->json($delivery);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $delivery = Delivery::findOrFail($id);

        $delivery->update($request->input());

        // Delete old incoming goods items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->delivery_items, 'id')));
        $delete_details = $delivery->delivery_items()->whereNotIn('id', $ids)->get();
        
        if($delete_details) {
          foreach ($delete_details as $detail) {
            // Calculate first, before deleting!
            $detail->item->increase($detail->unit_stock, 'FG');
            $detail->delete();
          }
        }

        $rows = $request->delivery_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $detail = $delivery->delivery_items()->find($row['id']);
            if($detail) {
                // Calculate stock on before the incoming Goods updated!
                $detail->item->increase($detail->unit_stock, 'FG');
                
                // update item row on the incoming Goods updated!
                $detail->update($row);
            }
            else{
                // create item row on the incoming Goods updated!
                $detail = $delivery->delivery_items()->create($row);
            }
            // Calculate stock on after the Deliveries updated!
            $detail->item->decrease($detail->unit_stock, 'FG');
        }

        $this->DATABASE::commit();
        return response()->json($delivery);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery = Delivery::findOrFail($id);
        $delivery->delivery_items()->delete();
        $delivery->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
