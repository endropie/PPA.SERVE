<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\ShipDeliveryItem as Filters;
use App\Http\Requests\Income\ShipDeliveryItem as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\ShipDeliveryItem;
use App\Traits\GenerateNumber;

class ShipDeliveryItems extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':            
                $ship_delivery_items = ShipDeliveryItem::with(['item','unit'])->filter($filters)->get();    
                break;

            case 'datagrid':    
                $ship_delivery_items = ShipDeliveryItem::with(['item','unit'])->filter($filters)->get();
                $ship_delivery_items->each->setAppends(['has_relationship']);
                break;

            default:
                $ship_delivery_items = ShipDeliveryItem::with(['item','unit'])->filter($filters)->collect();
                $ship_delivery_items->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($ship_delivery_items);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        foreach ($request->ship_delivery_items as $key => $row) {
            
            if($row['quantity'] > 0) {
                $ship_delivery_item = ShipDeliveryItem::create($row);
                $ship_delivery_item->pre_delivery_item_id = $row["pre_delivery_item_id"];
                $ship_delivery_item->save();
            }
        }

        $this->DATABASE::commit();
        return response()->json(['error' => false, 'message' => 'Items created']);
    }

    public function show($id)
    {
        $ship_delivery_item = ShipDeliveryItem::with([
            'item',
            'unit',
        ])->findOrFail($id);

        $ship_delivery_item->setAppends(['has_relationship']);

        return response()->json($ship_delivery_item);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $ship_delivery_item = ShipDeliveryItem::findOrFail($id);
        
        if ($ship_delivery_item->is_relationship == true) {
            return $this->error('SUBMIT FAIELD!', 'The data was relationship');
        }

        $ship_delivery_item->update($request->input());

        $this->DATABASE::commit();
        return response()->json($ship_delivery_item);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $ship_delivery_item = ShipDeliveryItem::findOrFail($id);
        
        if ($ship_delivery_item->is_relationship == true) {
            return $this->error('SUBMIT FAIELD!', 'The data was relationship');
        }
        $ship_delivery_item->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
