<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Income\PreDelivery as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Income\PreDelivery as Filters;
use App\Models\Income\PreDelivery; 
use App\Models\Income\PreDeliveryItem;
use App\Models\Income\RequestOrderItem;
use App\Traits\GenerateNumber;

class PreDeliveries extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filter)
    {
        switch (request('mode')) {
            case 'all':            
                $pre_deliveries = PreDelivery::filter($filter)->get();    
                break;

            case 'datagrid':    
                $pre_deliveries = PreDelivery::with(['customer'])->filter($filter)->get();
                $pre_deliveries->each->setAppends(['is_relationship']);
                break;

            case 'items':            
                $pre_deliveries = PreDeliveryItem::hasAmount()->get();    
                break;

            default:
                $pre_deliveries = PreDelivery::with(['customer'])->filter($filter)->collect();
                $pre_deliveries->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($pre_deliveries);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextPreDeliveryNumber()]);

        $pre_delivery = PreDelivery::create($request->all());

        $rows = $request->pre_delivery_items;
        for ($i=0; $i < count($rows); $i++) { 
            // create detail item created!
            $detail = $pre_delivery->pre_delivery_items()->create($rows[$i]);
            $detail->item->transfer($detail, $detail->unit_amount, 'PDO', 'RO');
        }

        $this->DATABASE::commit();
        return response()->json($pre_delivery);
    }

    public function show($id)
    {
        $pre_delivery = PreDelivery::with([
            'customer',
            'pre_delivery_items.item',
            'pre_delivery_items.unit'
        ])->findOrFail($id);

        $pre_delivery->setAppends(['has_relationship']);

        return response()->json($pre_delivery);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();
        $pre_delivery = PreDelivery::findOrFail($id);
        
        if ($pre_delivery->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be changed!');
        }

        // Delete old incoming goods items when $request detail rows has not ID
        if($pre_delivery->pre_delivery_items) {
            foreach ($pre_delivery->pre_delivery_items as $detail) {
              // Delete detail of "Request Order"
              $detail->item->distransfer($detail);
              $detail->delete();
            }
        }

        $rows = $request->pre_delivery_items;
        for ($i=0; $i < count($rows); $i++) { 
            // create detail item created!
            $detail = $pre_delivery->pre_delivery_items()->create($rows[$i]);
            $detail->item->transfer($detail, $detail->unit_amount, 'PDO', 'RO');
            
            if($detail->item->stock('PDO')->total < (0 + 0.1)) $this->error('Data is not allowed to be changed!');
            // abort(501, $detail->item_id .' -> '.$detail->item->stock('RO')->total .'->'. $detail->item->stock('PDO')->total);
        }

        $this->DATABASE::commit();
        return response()->json($pre_delivery);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $pre_delivery = PreDelivery::findOrFail($id);
        if ($pre_delivery->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be deleted!');
        }

        
        foreach ($pre_delivery->pre_delivery_items as $detail) {
            $detail->item->distransfer($detail);

            if($detail->item->stock('PDO')->total < (0 + 0.1)) $this->error('Data is not allowed to be deleted');
            $detail->delete();
        }
        $pre_delivery->delete();
        
        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }
}
