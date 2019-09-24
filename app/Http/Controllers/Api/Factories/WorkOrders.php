<?php

namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\WorkOrder as Filters;
use App\Http\Requests\Factory\WorkOrder as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\PackingItem;
use App\Models\Factory\WorkOrder;
use App\Models\Factory\WorkProductionItem;
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

            default:
                $work_orders = WorkOrder::with(['line', 'shift'])->filter($filter)->latest()->collect();
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
                    if($j == 0) $row_line["ismain"] = 1;
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
                $with = [
                    'work_order_items.work_order_item_lines.line',
                    'work_order_items.work_order_item_lines.work_production_items.work_production',
                    'work_order_items.work_order_item_lines',
                    'work_order_items.packing_items.packing',
                ];
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
        if(request('mode') == 'revision') return $this->revision($request, $id);

        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if($work_order->is_relationship) $this->error('The data has RELATIONSHIP, is not allowed to be Updated!');
        if($work_order->status != "OPEN") $this->error("The data not OPEN state, is not allowed to be Updated!");

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
                    if($j == 0) $row_line["ismain"] = 1;
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

        $work_order->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function revision(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();
        $revise = WorkOrder::findOrFail($id);

        $revise = WorkOrder::findOrFail($id);
        foreach ($revise->work_order_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        if($request->number) {
            $max = (int) WorkOrder::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number' => ($max + 1)]);
        }

        $work_order = WorkOrder::create($request->all());
        if($request->number) {
            $max = (int) WorkOrder::where('number', $request->number)->max('revise_number');
            $work_order->revise_number = ($max + 1);
            $work_order->status = $revise->status;
            $work_order->save();
        }

        $rows = $request->work_order_items ?? [];

        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);

            $detail->work_order_item_lines()->forceDelete();
            $detail->forceDelete();
        }

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // $oldDetail = $work_order->work_order_items()->find($row['id']);

            $detail = $work_order->work_order_items()->create($row);
            $detail->process = $row['process'];
            $detail->save();
            // Calculate stock on after Detail item updated!
            $FROM = $work_order->stockist_from;
            $detail->item->transfer($detail, $detail->unit_process, 'WIP', $FROM);

            $row_lines = $row['work_order_item_lines'] ?? [];
            for ($j=0; $j < count($row_lines); $j++) {
                $row_line = $row_lines[$j];
                $line = $detail->work_order_item_lines()->create($row_line);
                $row_productions = $row_line['work_production_items'] ?? [];
                foreach ($row_productions as $row_production) {
                    $work_production_item = WorkProductionItem::find($row_production['id']);
                    $work_production_item->item_id = $detail->item_id;
                    $work_production_item->work_order_item_line()->associate($line);
                    $work_production_item->save();
                }
            }


            $row_packing_items = $row['packing_items'] ?? [];
            foreach ($row_packing_items as $row_packing_item) {
                $packing_item = PackingItem::find($row_packing_item["id"]);
                $packing_item->item->distransfer($packing_item);
                $packing_item->update(['item_id' => $detail->item_id]);
                $packing_item = $packing_item->fresh();

                $packing_item->item->transfer($packing_item, $packing_item->unit_total, 'FG', 'WIP');
                $NG = (double) $packing_item->packing_item_faults()->sum('quantity');
                if ($NG > 0) $packing_item->item->transfer($packing_item, $NG, 'NG', 'WIP');
                $packing_item->amount_faulty = $NG * $packing_item->unit_rate;
                $packing_item->save();

                $packing_item->work_order_item()->associate($detail);
                $packing_item->save();
            }

            $detail->calculate();
        }

        // Delete [soft] relation when nnnnot has relation!
        $revise->work_order_items->each(function($detail) {
            $detail->work_order_item_lines->each(function($work_order_item_line) {
                $work_order_item_line->work_production_items->each(function($work_production_item) {
                    $work_production_item->item->distransfer($work_production_item);
                    $work_production_item->work_order_item_line()->associate(null);
                    $work_production_item->save();
                    $work_production_item->forceDelete();
                });
            });

            $detail->packing_items->each(function($packing_item) {
                if ($packing_item) {
                    $packing_item->work_order_item()->associate(null);
                    $packing_item->save();

                    $packing_item->item->distransfer($packing_item);
                    $packing_item->packing->delete();
                    $packing_item->packing_item_faults()->delete();
                    $packing_item->delete();
                }
            });


            $detail->calculate();
        });

        $revise->status = 'REVISED';
        $revise->revise_id = $work_order->id;
        $revise->save();
        $revise->delete();

        $this->DATABASE::commit();
        return response()->json($work_order);
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
