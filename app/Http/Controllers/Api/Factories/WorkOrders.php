<?php

namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\WorkOrder as Filters;
use App\Http\Requests\Factory\WorkOrder as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\WorkOrder; 
use App\Traits\GenerateNumber;
class WorkOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filter)
    {
        switch (request('mode')) {
            case 'all':            
            $work_orders = WorkOrder::filter($filter)->get();
            break;

            case 'datagrid':    
            $work_orders = WorkOrder::with(['line', 'work_order_items.item', 'work_order_items.work_order_item_lines'])->filter($filter)->get();
            break;

            case 'items': 
            $work_orders = WorkOrder::with('work_order_items')->filter($filter)->get()
              ->filter(function($x) {
                  return $x; //($x->unit_amount > $x->total_packing_item);
              });
            break;

            case 'itemsX':    
            $work_orders = \App\Models\Factory\WorkOrderItem::with([
                'work_order'=> function($q) use ($filter){ 
                    $q->filter($filter); 
                }
            ])->get()
              ->filter(function($x) {
                  return ($x->unit_amount > $x->total_packing_item);
              });
            break;
            
            case 'item-lines':    
                // $work_orders = \App\Models\Factory\WorkOrderItemLine::with([
                //     'work_order_item.work_order'=> function($q) { 
                //         $q->select(['id','number']); 
                //     },
                // ])->get();
                $work_orders = WorkOrder::with(['work_order_items.work_order_item_lines'])->get();
                
            break;

            default:
                $work_orders = WorkOrder::with(['line', 'work_order_items.item', 'work_order_items.work_order_item_lines'])->collect();                
                break;
        }

        return response()->json($work_orders);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextWorkOrderNumber()]);
       
        $work_order = WorkOrder::create($request->all());

        $rows = $request->work_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // create item row on the Work Orders updated!
            $detail = $work_order->work_order_items()->create($row);
            // Calculate stock on after the Work Orders updated!
            $FROM = $work_order->stockist_from;
            $detail->item->transfer($detail, $detail->unit_amount, 'WO', $FROM);

            $row_lines = $row['work_order_item_lines'];
            if($row_lines) {
                for ($j=0; $j < count($row_lines); $j++) { 
                    $row_line = $row_lines[$j];
                    $detail->work_order_item_lines()->create($row_line);
                }
            }
        }
        
        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function show($id)
    {
        $work_order = WorkOrder::with([
            'line',
            'work_order_items.unit', 
            'work_order_items.item.unit',
            'work_order_items.work_order_item_lines.line'
        ])->findOrFail($id);

        // $work_order->has_relationship = $this->relationships($work_order, ['workin_production_items' => 'WorkIn Production']);

        return response()->json($work_order);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);
        $work_order->update($request->input());

        $rows = $request->work_order_items;

        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);

            $detail->work_order_item_lines()->delete();
            $detail->delete();
        }

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // $oldDetail = $work_order->work_order_items()->find($row['id']);

            $detail = $work_order->work_order_items()->create($row);
            // Calculate stock on after Detail item updated!
            $FROM = $work_order->stockist_from;
            $detail->item->transfer($detail, $detail->unit_amount, 'WO', $FROM);
            
            $row_lines = $row['work_order_item_lines'];
            if($row_lines) {

                for ($j=0; $j < count($row_lines); $j++) { 
                    $row_line = $row_lines[$j];
                    $detail->work_order_item_lines()->create($row_line);
                }
            }
        }
        
        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);
        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);

            $detail->work_order_item_lines()->delete();
            $detail->delete();
        }

        $work_order->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
