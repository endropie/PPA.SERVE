<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\ShipDelivery as Filters;
use App\Http\Requests\Income\ShipDelivery as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\ShipDelivery; 
use App\Models\Income\ShipDeliveryItem; 
use App\Models\Income\RequestOrder;
use App\Models\Income\RequestOrderItem;
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
                $ship_deliveries = ShipDelivery::with(['customer','operator'])->filter($filters)->get();
                $ship_deliveries->each->setAppends(['is_relationship']);
                break;

            default:
                $ship_deliveries = ShipDelivery::filter($filters)->with(['operator', 
                    'delivery_orders' => function($q) { $q->select(['id', 'ship_delivery_id', 'number', 'numrev']);},
                    'customer' => function($q) { $q->select(['id', 'name']);}
                ])->collect();
                $ship_deliveries->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($ship_deliveries);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextShipDeliveryNumber()]);

        $ship_delivery = ShipDelivery::create($request->all());

        $rows = collect($request->ship_delivery_items);

        if($rows->count() > 0) {
            $ids = $rows->pluck('id');
            $ship_delivery_items = ShipDeliveryItem::whereIn('id',$ids)
                ->wait()
                ->update(['ship_delivery_id' => $ship_delivery->id]);
            $this->storeDeliveryOrder($ship_delivery);
        }
        else {
            abort(501, 'Part detail not found!');
        }

        $this->DATABASE::commit();
        return response()->json($ship_delivery);
    }

    public function show($id)
    {
        $ship_delivery = ShipDelivery::with([
            'customer',
            'ship_delivery_items.item.item_units',
            'ship_delivery_items.unit'
        ])->findOrFail($id);
        
        $ship_delivery->setAppends(['has_relationship']);

        return response()->json($ship_delivery);
    }

    public function storeRequestOrder($ship_delivery) 
    {
        if ($ship_delivery->customer->order_mode == "ACCUMULATE") {
            $model = RequestOrder::where(function ($query) use ($ship_delivery) {
                $query->whereDate('begin_date' , '<=', $ship_delivery->date)
                      ->whereDate('until_date' , '>=', $ship_delivery->date);
              })->where('customer_id', $ship_delivery->customer_id)
                ->where('order_mode', $ship_delivery->customer->order_mode)
                ->latest()->first();

            if(!$model) {
                $model = new RequestOrder;
                $begin = Carbon()->parse($ship_delivery->date)->startOfMonth()->format('Y-m-d');
                $until = Carbon()->parse($ship_delivery->date)->endOfMonth()->format('Y-m-d');
                $order_mode = $ship_delivery->customer->order_mode;

                $model->date  = $ship_delivery->date;
                $model->begin_date  = $begin;
                $model->until_date  = $until;
                $model->customer_id = $ship_delivery->customer_id;
                $model->order_mode   = $order_mode;
                $model->description   = "ACCUMULATE P/O. FOR ". $begin." - ". $until;
                // For model update 
                if(!$model->id) {
                    $model->number = $this->getNextRequestOrderNumber($ship_delivery->date);
                }
                $model->save();

                $ship_delivery->request_order_id = $model->id;
                $ship_delivery->save();
            }

            $rows = $ship_delivery->ship_delivery_items
                ->groupBy('item_id')
                ->map(function($group) {
                    return [
                        'item_id' => $group[0]->item_id,
                        'unit_id' => $group[0]->item->unit_id,
                        'unit_rate' => 1,
                        'quantity' => $group->sum('unit_amount'),
                        'price' => 0,
                    ];
                });

            foreach ($rows as $key => $row) {
                // $fields = collect($row)->only(['item_id', 'unit_id', 'unit_rate', 'quantity'])->merge(['price'=>0])->toArray();
                
                $detail = $model->request_order_items()->create($row);

                // COMPUTE ITEMSTOCK !!
                // $detail->item->transfer($detail, $detail->unit_amount, 'RO');
                $detail->ship_delivery_id = $ship_delivery->id;
                $detail->save();
            }
        }
    }

    public function storeDeliveryOrder($ship_delivery) {

        $this->storeRequestOrder($ship_delivery);

        $delivery_orders = $ship_delivery->delivery_orders;
        foreach ($delivery_orders as $delivery_order) {
            foreach ($delivery_order->delivery_order_items as $detail) {
                $detail->item->distransfer($detail);
                $detail->delete();
            }
            $delivery_order->delete();
        }

        $list = [];
        $request_order_items = RequestOrderItem::whereHas('request_order', function($q) use($ship_delivery) {
            $q->where('customer_id', $ship_delivery->customer_id);
          })
          ->get()
          ->filter(function($x) {
            return ($x->unit_amount > $x->total_delivery_order_item);
          })
          ->map(function($detail) {
            $detail->sort_date = $detail->request_order->date;
            return $detail;
          })->sortBy('sort_date'); 

        $rows = $ship_delivery->ship_delivery_items->groupBy('item_id')->map(function($group, $key){
            return $group->sum('unit_amount');
        });

        // $this->error($request_order_items);

        foreach ($request_order_items as $detail) {
            if(isset($rows[$detail->item_id])) {

                $max_amount = $rows[$detail->item_id];
                $unit_amount = $detail->unit_amount - $detail->total_delivery_order_item;
                $unit_amount = ($max_amount < $unit_amount ? $max_amount : $unit_amount);

                $rows[$detail->item_id] -= $unit_amount;
                
                if($unit_amount > 0 ){
                    $RO = $detail->request_order_id;
                    $DTL = $detail->id;
    
                    $list[$RO][$DTL] = [
                        'item_id' => $detail->item_id,
                        'unit_id' => $detail->item->unit_id,
                        'unit_rate' => 1,
                        'quantity' => $unit_amount
                    ];
                }
            }
        }

        foreach ($list as $RO => $rows) {
            $delivery_order = $ship_delivery->delivery_orders()->create([
                'number' => $this->getNextDeliveryOrderNumber(),
                'transaction' => 'REGULER',
                'customer_id' => $ship_delivery->customer_id,
                'customer_name' => $ship_delivery->customer_name,
                'customer_phone' => $ship_delivery->customer_phone,
                'customer_address' => $ship_delivery->customer_address,

                'operator_id' => $ship_delivery->operator_id,
                'date' => $ship_delivery->date,
                'time' => $ship_delivery->time,
                'due_date' => $ship_delivery->due_date,
                'due_time' => $ship_delivery->due_time,
            ]);

            foreach ($rows as $DTL => $row) {

                $detail = $delivery_order->delivery_order_items()->create($row);
                $detail->item->transfer($detail, $detail->unit_amount, null, 'FG');

                $detail->request_order_item_id = $DTL;
                $detail->save();
            }
            
            $delivery_order->request_order_id = $RO;
            $delivery_order->save();
        }

    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $ship_delivery = ShipDelivery::findOrFail($id);        

        $latest = ShipDelivery::latest('created_at')->first();
        
        if ($ship_delivery->is_relationship == true) $this->error('The data has relationships, is not allowed to be changed !');
        

        if ($latest->id != $ship_delivery->id) $this->error('Data cannot to deleted, For delete latest only!');
        

        foreach ($ship_delivery->delivery_orders as $delivery_order) {
            foreach ($delivery_order->delivery_order_items as $detail) {
                $detail->item->distransfer($detail);
                $detail->delete();
            }
            $delivery_order->delete();
        }

        if ($ship_delivery->customer->order_mode == 'ACCUMULATE') {
            $request_order_items = RequestOrderItem::where('ship_delivery_id', $ship_delivery->id);
            
            $request_order_items->each(function($detail) {
                $detail->delete();

                $request_order = RequestOrder::find($detail->request_order_id);
                if($request_order->request_order_items->count() == 0) $request_order->delete();
            });
        }

        $ship_delivery->ship_delivery_items()->update(['ship_delivery_id' => null]);
        $ship_delivery->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
