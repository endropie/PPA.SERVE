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
                $work_orders = WorkOrder::with(['customer', 'work_order_items.line', 'work_order_items.item'])->collect();                
                break;
        }

        return response()->json($work_orders);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextWorkOrderNumber()]);
       
        $work_order = WorkOrder::create($request->all());

        $row = $request->work_order_items;

        // Work Order Items only 1 row detail (relation = $model->hasOne)
        if($row) {
            // create item row on the Work Orders updated!
            $detail = $work_order->work_order_items()->create($row);
    
            // Calculate stock on after the Work Orders updated!
            $detail->item->increase($detail->unit_stock, 'WO', 'FM');
        }
        
        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function show($id)
    {
        $work_order = WorkOrder::with(['work_order_items.item.item_units', 'work_order_items.item.unit'])->findOrFail($id);

        $work_order->hasRelationship = $this->relationships($work_order, ['workin_production_items' => 'WorkIn Production']);

        return response()->json($work_order);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);
        $work_order->update($request->input());

        $row = $request->work_order_items;
        
        // Work Order Items only 1 row detail (relation = $model->hasOne)
        if($row) {
            $detail = $work_order->work_order_items()->find($row['id']);

            if($detail) {
                // Calculate stock on before the Work Orders updated!
                $detail->item->decrease($detail->unit_stock, 'WO', 'FM');
                
                // update item row on the Work Orders updated!
                $detail->update($row);
            }
            else{
                // create item row on the Work Orders updated!
                $detail = $work_order->work_order_items()->create($row);
            }

            // Calculate stock on before the Work Orders updated!
            $detail = $work_order->work_order_items()->find($detail->id);
            $detail->item->increase($detail->unit_stock, 'WO', 'FM');
        }
        
        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function destroy($id)
    {
        $work_order = WorkOrder::findOrFail($id);
        $work_order->work_order_items()->delete();
        $work_order->delete();

        return response()->json(['success' => true]);
    }
}
