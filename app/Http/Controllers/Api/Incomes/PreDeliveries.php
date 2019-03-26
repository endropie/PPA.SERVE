<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Income\PreDelivery as Request;
use App\Http\Controllers\ApiController;

use App\Models\Income\PreDelivery; 
use App\Traits\GenerateNumber;

class PreDeliveries extends ApiController
{
    use GenerateNumber;

    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $pre_deliveries = PreDelivery::filterable()->get();    
                break;

            case 'datagrid':    
                $pre_deliveries = PreDelivery::with(['customer'])->filterable()->get();
                
                break;

            default:
                $pre_deliveries = PreDelivery::with(['customer'])->collect();                
                break;
        }

        return response()->json($pre_deliveries);
    }

    public function store(Request $request)
    {
        if(!$request->number) $request->merge(['number'=> $this->getNextPreDeliveryNumber()]);

        $pre_delivery = PreDelivery::create($request->all());

        $item = $request->pre_delivery_items;
        for ($i=0; $i < count($item); $i++) { 

            // create item production on the incoming Goods updated!
            $pre_delivery->pre_delivery_items()->create($item[$i]);
        }

        return response()->json($pre_delivery);
    }

    public function show($id)
    {
        $pre_delivery = PreDelivery::with(['pre_delivery_items.item', 'pre_delivery_items.unit'])->findOrFail($id);
        $pre_delivery->is_editable = (!$pre_delivery->is_related);

        return response()->json($pre_delivery);
    }

    public function update(Request $request, $id)
    {
        $pre_delivery = PreDelivery::findOrFail($id);

        $pre_delivery->update($request->input());

        // Delete items on the incoming goods updated!
        $pre_delivery->pre_delivery_items()->delete();

        $item = $request->pre_delivery_items;
        for ($i=0; $i < count($item); $i++) { 

            // create item row on the incoming Goods updated!
            $pre_delivery->pre_delivery_items()->create($item[$i]);
        }

        return response()->json($pre_delivery);
    }

    public function destroy($id)
    {
        $pre_delivery = PreDelivery::findOrFail($id);
        $pre_delivery->delete();

        return response()->json(['success' => true]);
    }
}
