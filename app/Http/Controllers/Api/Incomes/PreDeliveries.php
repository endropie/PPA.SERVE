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

        $item = $request->pre_delivery_items;
        for ($i=0; $i < count($item); $i++) { 
            // create detail item created!
            $pre_delivery_item = $pre_delivery->pre_delivery_items()->create($item[$i]);
            $this->storeMountUnitAmount($pre_delivery, $pre_delivery_item);
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
        // dd($pre_delivery->relationship(['pre_delivery_items.request_order_items']));

        return response()->json($pre_delivery);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $pre_delivery = PreDelivery::findOrFail($id);
        if ($pre_delivery->is_relationship == true) {
            return $this->error('SUBMIT FAIELD!', 'The data was relationship');
        }

        $pre_delivery->pre_delivery_items->map(function($detail) {
            $detail->request_order_items()->delete();
            $detail->delete();
        });
        $pre_delivery->delete();

        
        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }

    public function storeMountUnitAmount($pre_delivery, $pre_delivery_item) {
        if ($pre_delivery->order_mode == 'PO') {
            //Code..
            abort(501, 'is POmode');
        }
        else {
            $request_order_items = RequestOrderItem::with('item')
              ->where('item_id', $pre_delivery_item->item_id)
              ->whereHas('request_order', function($q) use($pre_delivery) {
                $q->where('customer_id', $pre_delivery->customer_id);
              })->get();
              

            $item_amount = $pre_delivery_item->unit_amount;
            $assosiate = collect([]);

            for ($i=0; $i < count($request_order_items); $i++) { 
                $detail = $request_order_items[$i];
                $detail->unit_available = ( $detail->unit_amount - $detail->total_pre_delivery_item );

                if ($item_amount <= 0) break;
                if ($detail->unit_available <= 0)  continue;

                $quantity = $item_amount > $detail->unit_available ? $detail->unit_available : $item_amount;

                if($quantity > 0) {
                    $assosiate->push([
                        'base_type' => get_class($detail),
                        'base_id' => $detail->id,
                        'unit_amount' => $quantity
                    ]);
                }

                $item_amount -= $quantity;
            }

            if($item_amount > 0.5) {
                $name = $detail->item->part_name;
                $code = $detail->item->code;

                $message = 'Item '. ($code ? '['.$code.'] ': '') . ($name ?? '').' total unit invalid!';
                // abort()
                abort(501, $message);
            }
            $pre_delivery_item->request_order_items()->createMany($assosiate->toArray());

            // $this->error(501, 'Item unit amount invalid!', $assosiate);
        }
    }

}
