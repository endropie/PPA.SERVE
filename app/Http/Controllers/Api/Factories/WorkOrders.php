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
            $work_orders = WorkOrder::filter($filter)->latest()->get();
            break;

            case 'datagrid':
            $work_orders = WorkOrder::with(['line',
              'work_order_items.item',
              'work_order_items.work_order_item_lines'
            ])->filter($filter)->get();
            break;

            case 'packings':
            $work_orders = WorkOrder::with('work_order_items')->filter($filter)->latest()->get();
            break;

            case 'item-lines':
                $work_orders = WorkOrder::with([
                  'work_order_items.work_order_item_lines'
                ])->filter($filter)->latest()->get();

            break;

            default:
                $work_orders = WorkOrder::with([
                  'line', 'shift',
                  'work_order_items.item',
                  'work_order_items.work_order_item_lines'
                ])->filter($filter)->latest()->collect();
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
        switch (request('mode')) {
            case 'prelines':
                $with = ['work_order_item_lines.line'];
                break;

            default:
                $with = ['work_order_items.work_order_item_lines'];
                break;
        }

        $work_order = WorkOrder::with(
          array_merge([
            'line', 'shift',
            'work_order_items.unit',
            'work_order_items.item.unit',
            'work_order_items.item.item_units',
          ], $with)
        )->withTrashed()->findOrFail($id);

        $work_order->setAppends(['is_relationship', 'has_relationship']);

        return response()->json($work_order);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') == 'processed') return $this->processed($request, $id);

        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if($work_order->is_relationship) $this->error('The data has RELATIONSHIP, is not allowed to be Deleted!');
        if($work_order->status != "OPEN") $this->error("The data not OPEN state, is not allowed to be Deleted!");

        $work_order->update($request->input());

        $rows = $request->work_order_items;

        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);

            $detail->work_order_item_lines()->forceDelete();
            $detail->forceDelete();
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

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($work_order->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode!");
        if($mode == "DELETED" && $work_order->status != "OPEN") $this->error("The data $work_order->status state, is not allowed to be $mode!");

        if ($mode == "VOID") {
            $work_order->status = 'VOID';
            $work_order->save();
        }

        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);

            $detail->work_order_item_lines()->delete();
            $detail->delete();
        }

        // $this->error('LOLOS');
        $work_order->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function processed(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if(in_array($work_order->status, ["PROCESSED", "VOID"])) $this->error("WO [#$work_order->number] Cannot to Confirmed!");

        $rows = $request->work_order_items;

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // $oldDetail = $work_order->work_order_items()->find($row['id']);

            $detail = $work_order->work_order_items()->findOrFail($row["id"]);


            if( $row["process"] > $detail->quantity) $this->error("Detail [$detail->id] Quantity Process Invalid!");

            $detail->process = $row["process"];

            $detail->save();
            $detail->calculate('process');

            // Calculate Over Stock Quantity at item processed!
            if ($detail->amount_process < $detail->unit_amount) {
                $FROM = $work_order->stockist_from;
                $OVER = ($detail->unit_amount - $detail->amount_process);
                $detail->item->transfer($detail, $OVER, $FROM, 'WO');
            }

            // Calculate stock on Detail item processed!
            $detail->item->transfer($detail, $detail->amount_process,  'WIP', 'WO');
        }

        $work_order->processed_by = \Auth::user()->id ?? null;
        $work_order->processed_at = now();
        $work_order->status = 'PROCESSED';
        $work_order->save();

        $this->DATABASE::commit();
        return response()->json($work_order);
    }
}
