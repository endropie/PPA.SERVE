<?php

namespace App\Http\Controllers\Api\Factories;

use App\Http\Requests\Factory\WorkOrder as Request;
use App\Http\Controllers\ApiController;

use App\Models\Factory\WorkOrder; 
use App\Traits\GenerateNumber;

class WorkOrders extends ApiController
{
    use GenerateNumber;

    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $work_orders = WorkOrder::filterable()->get();    
                break;

            case 'datagrid':    
                $work_orders = WorkOrder::with(['customer', 'work_order_items.line', 'work_order_items.item'])->filterable()->get();
                
                break;

            default:
                $work_orders = WorkOrder::collect();                
                break;
        }

        return response()->json($work_orders);
    }

    public function store(Request $request)
    {
        if(!$request->number) $request->merge(['number'=> $this->getNextWorkOrderNumber()]);
       
        $work_order = WorkOrder::create($request->all());

        $item = $request->work_order_items;

        // create item production on the Work-order created!
        $work_order->work_order_items()->create($item);

        return response()->json($work_order);
    }

    public function storeGroup(Request $request)
    {
        
        $group = $request->workgroup_items ?? [];
        
        foreach ($group as $item) {

            if (!$item['number']) $item['number'] =  $this->getNextWorkOrderNumber();

            $work_order = WorkOrder::create([
                'number' => $item['number'],
                'customer_id' => $request->customer_id,
                'description' => $request->description
            ]);

            // create item production on the Work-order group created!
            $work_order->work_order_items()->create($item);
        }

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $work_order = WorkOrder::with(['work_order_items.item'])->findOrFail($id);
        $work_order->is_editable = (!$work_order->is_related);

        return response()->json($work_order);
    }

    public function update(Request $request, $id)
    {
        $work_order = WorkOrder::findOrFail($id);

        $work_order->update($request->input());

        // Delete items on the incoming goods updated!
        $work_order->work_order_items()->delete();

        $item = $request->work_order_items;
        
        // create item row on the incoming Goods updated!
        $work_order->work_order_items()->create($item);

        return response()->json($work_order);
    }

    public function destroy($id)
    {
        $work_order = WorkOrder::findOrFail($id);
        $work_order->delete();

        return response()->json(['success' => true]);
    }
}
