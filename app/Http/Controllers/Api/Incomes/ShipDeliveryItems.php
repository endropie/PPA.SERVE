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
                $detail = ShipDeliveryItem::create($row);
                if($detail->item->stock('PDO')->total < ($detail->unit_amount - 0.1)) $this->error('Data is not allowed to be created!');
                $detail->item->transfer($detail, $detail->unit_amount, null, 'PDO');
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

        $detail = ShipDeliveryItem::findOrFail($id);
        
        if ($detail->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be changed');
        }

        $detail->item->distransfer($detail);

        $detail->update($request->input());

        if($detail->item->stock('PDO')->total < ($detail->unit_amount - 0.1)) $this->error('Data is not allowed to be updated!');
        $detail->item->transfer($detail, $detail->unit_amount, null, 'PDO');

        $this->DATABASE::commit();
        return response()->json($detail);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $detail = ShipDeliveryItem::findOrFail($id);
        
        if ($detail->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be deleted');
        }
        
        $detail->item->distransfer($detail);
        $detail->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
