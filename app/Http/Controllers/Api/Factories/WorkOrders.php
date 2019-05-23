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
            $work_orders = WorkOrder::filterable()->get();    
            break;

            case 'datagrid':    
            $work_orders = WorkOrder::with(['line', 'work_order_items.item', 'work_order_items.work_order_item_lines'])->filterable()->get();
            break;

            // case 'items':    
            // $work_orders = \App\Models\Factory\WorkOrderItem::with([
            //     'work_order'=> function($q) { $q->select(['id','number']); },
            // ])->get();
            // break;
            
            // case 'item-lines':    
            //     // $work_orders = \App\Models\Factory\WorkOrderItemLine::with([
            //     //     'work_order_item.work_order'=> function($q) { 
            //     //         $q->select(['id','number']); 
            //     //     },
            //     // ])->get();
            //     $work_orders = WorkOrder::with(['work_order_items.work_order_item_lines'])->get();
                
            // break;

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
            $From = $work_order->stockist_from;
            $detail->item->increase($detail->unit_amount, 'WO', $From);

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

        $deletes = $work_order->work_order_items()->whereNotIn('id', array_filter(array_column($rows, 'id')))->get();
        foreach ($deletes as $detail) {
            $From = $work_order->stockist_from;
            $detail->item->decrease($detail->unit_amount, 'WO', $From);
            $detail->delete();
        }

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $oldDetail = $work_order->work_order_items()->find($row['id']);
            if($oldDetail) {
                // Calculate stock on before the incoming Goods updated!
                $From = $work_order->stockist_from;
                $oldDetail->item->decrease($oldDetail->unit_amount, 'WO', $From);
            }

            // Update or Create detail row
            // if($row['stockist_from'] != 'FM') abort(501, $row['stockist_from']);

            $newDetail = $work_order->work_order_items()->updateOrCreate(['id' => $row['id']], $row);
            // Calculate stock on after Detail item updated!
            $From = $newDetail->stockist_from;
            $newDetail->item->increase($newDetail->unit_amount, 'WO', $From);

            $row_lines = $row['work_order_item_lines'];
            if($row_lines) {
                $newDetail->work_order_item_lines()
                    ->whereNotIn('id', array_filter(array_column($row_lines, 'id')))
                    ->delete();

                for ($j=0; $j < count($row_lines); $j++) { 
                    $row_line = $row_lines[$j];
                    $newDetail->work_order_item_lines()->updateOrCreate(['id' => $row_line['id'] ?? null ],$row_line);
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
        $work_order->work_order_items->map(function($detail) use($work_order) {
            $detail->item->decrease($detail->unit_amount, 'WO', $work_order->stockist_from);
        });
        $work_order->work_order_items()->delete();
        $work_order->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
