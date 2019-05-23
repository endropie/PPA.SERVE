<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\ShipDelivery as Filters;
use App\Http\Requests\Income\ShipDelivery as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\ShipDelivery; 
use App\Models\Income\ShipDeliveryItem; 
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
                $ship_deliveries = ShipDelivery::with(['customer','operator','vehicle'])->filterable()->get();
                $ship_deliveries->each->setAppends(['is_relationship']);
                break;

            default:
                $ship_deliveries = ShipDelivery::with(['operator','vehicle', 
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
            $this->storeDeliveryOrder($ship_delivery, $rows);
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

    public function storeDeliveryOrder($ship_delivery) {
        $delivery_orders = $ship_delivery->delivery_orders;
        foreach ($delivery_orders as $delivery_order) {
            foreach ($delivery_order->delivery_order_items as $delivery_order_item) {
                $delivery_order_item->item->increase($delivery_order_item->unit_amount, 'FG');
                $delivery_order_item->ship_delivery_items()->delete();
                $delivery_order_item->request_order_items()->delete();
                $delivery_order_item->delete();
            }
            $delivery_order->delete();
        }

        $list = []; $extract=[]; $uses=[];

        $request_order_items = RequestOrderItem::whereHas('request_order', function($q) use($ship_delivery) {
            $q->where('customer_id', $ship_delivery->customer_id);
        })->get()->filter(function($x) {
            return ($x->unit_amount > $x->total_delivery_order_item);
        });

        // abort(500, json_encode($request_order_items->count()));


        foreach ($ship_delivery->ship_delivery_items as $ship_delivery_item) {
            $max_amount = $ship_delivery_item->unit_amount;
            $sum_amount = 0;

            // foreach ($ship_delivery_item->pre_delivery_item->request_order_items as $key => $base_item) {
            
            foreach ($request_order_items as $key => $base_item) {

                // abort(500, json_encode($base_item));
                if($base_item->item_id == $ship_delivery_item->item_id) {
                    $unit_amount = $base_item->unit_amount - ($uses[$base_item->id] ?? 0);
                    // if (isset($uses[$base_item->id])) abort(500, json_encode($uses));

                    $unit_amount = ($max_amount > $unit_amount ? $unit_amount : $max_amount);
                    $max_amount -= $unit_amount;
                    $sum_amount += $unit_amount;

                    if(!isset($uses[$base_item->id])) $uses[$base_item->id] = 0;
                    $uses[$base_item->id] += $unit_amount;
                    
                    if($unit_amount > 0 ){
                        $RO = $base_item->request_order_id;
                        $ITEM = $base_item->id;
        
                        $list[$RO][$ITEM] = [
                            'item_id' => $ship_delivery_item->item_id,
                            'unit_id' => $ship_delivery_item->item->unit_id,
                            'unit_rate' => 1,
                            'quantity' => $sum_amount
                        ];
        
                        $extract[$RO][$ITEM][] = [
                            'base_id' => $ship_delivery_item->id,
                            'base_type' => get_class($ship_delivery_item),
                            'unit_amount' => ($unit_amount)
                        ];

                    }
                }

            }

            //  if($max_amount > 0.1) abort(501, 'Total unit invalid! --> '. $max_amount .' from '. $ship_delivery_item->unit_amount);
        }

        // abort(500, json_encode('tetete'));

        foreach ($list as $ID => $rows) {
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

            foreach ($rows as $ITEM => $row) {
                $row['quantity'] = 0;

                foreach ($extract[$ID][$ITEM] as $val) {
                    $row['quantity'] += $val['unit_amount'];
                }
                
                // abort(501, json_encode($row['quantity']));

                $newDetail = $delivery_order->delivery_order_items()->create($row);
                $newDetail->item->decrease($newDetail->unit_amount, 'FG');
                // $newDetail->request_order_item_id = $row['request_order_item_id'];
                $newDetail->ship_delivery_items()->createMany($extract[$ID][$ITEM]);
                $newDetail->request_order_items()->create([
                    'base_type' => get_class($newDetail),
                    'base_id' => $newDetail->id,
                    'unit_amount' => $newDetail->unit_amount,
                ]);
            }
            
            $delivery_order->request_order_id = $ID;
            $delivery_order->save();
        }

    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $ship_delivery = ShipDelivery::findOrFail($id);
        
        if ($ship_delivery->is_relationship == true) {
            return $this->error('SUBMIT FAIELD!', 'The data was relationship');
        }

        foreach ($ship_delivery->delivery_orders as $delivery_order) {
            foreach ($delivery_order->delivery_order_items as $delivery_order_item) {
                $delivery_order_item->item->increase($delivery_order_item->unit_amount, 'FG');
                $delivery_order_item->ship_delivery_items()->delete();
                $delivery_order_item->request_order_items()->delete();
                $delivery_order_item->delete();
            }
            $delivery_order->delete();
        }

        $ship_delivery->ship_delivery_items()->update(['ship_delivery_id' => null]);
        $ship_delivery->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
