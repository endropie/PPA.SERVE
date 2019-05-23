<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Income\RequestOrder as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Income\RequestOrder as Filters;
use App\Models\Income\RequestOrder; 
use App\Traits\GenerateNumber;

class RequestOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        $fields = request('fields');
        $fields = $fields ? explode(',', $fields) : [];

        switch (request('mode')) {
            case 'all':
                $request_orders = RequestOrder::filter($filters)->get();    
                break;

            case 'datagrid':
                $request_orders = RequestOrder::with(['customer'])->filterable()->get();
                $request_orders->each->setAppends(['is_relationship']);
                break;
            
            case 'detail-items':
                $request_orders = RequestOrder::with('request_order_items')->filterable()->get(array_merge(['id'], $fields));
            break;

            case 'has-items':
                $fields = request('fields');
                $fields = $fields ? explode(',', $fields) : [];
                
                $request_orders = RequestOrder::filterable()->get(array_merge(['id'], $fields));
                $request_orders = $request_orders->map(function($rs) {
                    return array_merge($rs->toArray(), 
                        ['has_items' => $rs->request_order_items->mapToGroups(function($item, $key) {
                            return [$item->item_id => $item->id];
                        })]
                    );
                });
                break;

            default:
                $request_orders = RequestOrder::with(['customer'])->filter($filters)->collect();
                $request_orders->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        $request_orders->map(function($row) {
            $row->request_order_items->each->setAppends(['unit_amount','total_pre_delivery_item']);
        });

        return response()->json($request_orders);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextRequestOrderNumber()]);

        $request_order = RequestOrder::create($request->all());

        $item = $request->request_order_items;
        for ($i=0; $i < count($item); $i++) { 

            // create item production on the request orders updated!
            $request_order->request_order_items()->create($item[$i]);
        }

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function show($id)
    {
        $request_order = RequestOrder::with([
            'customer',
            'request_order_items.item.item_units',
            'request_order_items.unit'
        ])->findOrFail($id);

        $request_order->setAppends(['has_relationship']);

        // dd($request_order->total_delivery_order_item);

        return response()->json($request_order);
    }

    public function update(Request $request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);
        
        if ($request_order->is_relationship == true) {
            return $this->error('SUBMIT FAIELD!', 'The data was relationship');
        }

        $request_order->update($request->input());

        // Delete old incoming goods items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->request_order_items, 'id')));
        $delete_details = $request_order->request_order_items()->whereNotIn('id', $ids)->get();
        
        if($delete_details) {
          foreach ($delete_details as $detail) {
            // Delete detail of "Request Order"
            $detail->delete();
          }
        }
        
        $rows = $request->request_order_items;
        for ($i=0; $i < count($rows); $i++) { 
            $row = $rows[$i];
            $detail = $request_order->request_order_items()->find($row['id']);
            if($detail) {                
                // update item row on the request orders updated!
                $detail->update($row);
            }
            else{
                // create item row on the request orders updated!
                $request_order->request_order_items()->create($row);
            }
        }

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();
        
        $request_order = RequestOrder::findOrFail($id);
        
        if ($request_order->is_relationship == true) {
            return $this->error('SUBMIT FAIELD!', 'The data was relationship');
        }

        $request_order->request_order_items()->delete();
        $request_order->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
