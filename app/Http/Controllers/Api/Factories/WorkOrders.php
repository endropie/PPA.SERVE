<?php

namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\WorkOrder as Filter;
use App\Filters\Factory\WorkOrderItem as FilterItem;
use App\Filters\Factory\WorkOrderItemLine as FilterItemLine;
use App\Http\Requests\Factory\WorkOrder as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\PackingItem;
use App\Models\Factory\WorkOrder;
use App\Models\Factory\WorkOrderItem;
use App\Models\Factory\WorkOrderItemLine;
use App\Models\Factory\WorkProductionItem;
use App\Traits\GenerateNumber;
class WorkOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter, FilterItem $filterItem, FilterItemLine $filterItemLine)
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

            case 'items':
            $work_orders = WorkOrderItem::with(['item','work_order'])->filter($filterItem)->get();
            break;

            case 'lines':
            $work_orders = WorkOrderItemLine::with(['work_order_item.item','work_order_item.work_order'])->filter($filterItemLine)->get();
            break;

            default:
                $work_orders = WorkOrder::with(['line', 'shift', 'work_order_items.item.unit'])->filter($filter)->latest()->collect();
                $work_orders->getCollection()->transform(function($row) {
                    $row->append(['is_relationship', 'total_production', 'total_packing', 'total_amount', 'has_producted', 'has_packed']);
                    return $row;
                });

                break;
        }

        return response()->json($work_orders);
    }

    public function items(Filter $filter, FilterItemLine $filter_item_line)
    {
        switch (request('mode')) {
            case 'all':
            $work_order_items = WorkOrderItem::filter($filter)->latest()->get();
            break;

            default:
                $work_order_items = WorkOrderItemLine::with(['line', 'work_order_item.work_order.shift', 'work_order_item.item', 'work_order_item.unit'])
                  ->filter($filter_item_line)
                  ->latest()->collect();

                $work_order_items->getCollection()->transform(function($row) {
                    return $row;
                });

                break;
        }

        return response()->json($work_order_items);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextWorkOrderNumber()]);

        $work_order = WorkOrder::create($request->all());

        $rows = $request->work_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            ## create item row on the Work Orders updated!
            $detail = $work_order->work_order_items()->create($row);
            ## Calculate stock on after the Work Orders updated!
            $detail->item->transfer($detail, $detail->unit_amount, 'WO');

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

        $work_order->append(['is_relationship', 'has_relationship', 'total_production', 'total_packing', 'has_producted', 'has_packed']);

        return response()->json($work_order);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') == 'revision') return $this->error('NOT SUPPORTED'); //$this->revision($request, $id);
        if(request('mode') == 'producted') return $this->producted($request, $id);
        if(request('mode') == 'packed') return $this->packed($request, $id);
        if(request('mode') == 'closed') return $this->closed($request, $id);
        if(request('mode') == 'reopen') return $this->reopen($request, $id);

        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if($work_order->is_relationship) $this->error("[$work_order->number] has RELATIONSHIP, is not allowed to be Updated!");
        if($work_order->status != "OPEN") $this->error("[$work_order->number] not OPEN state, is not allowed to be Updated!");

        $work_order->update($request->input());

        $rows = $request->work_order_items;

        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);

            $detail->work_order_item_lines()->forceDelete();
            $detail->forceDelete();
        }

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $detail = $work_order->work_order_items()->create($row);
            ## Calculate stock on after Detail item updated!
            $detail->item->transfer($detail, $detail->unit_amount, 'WO');

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
        if($work_order->is_relationship) $this->error("[$work_order->number] has RELATIONSHIP, is not allowed to be $mode!");
        if($mode == "DELETED" && $work_order->status != "OPEN") $this->error("The data $work_order->status state, is not allowed to be $mode!");

        if ($mode == "VOID") $work_order->moveState('VOID');

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

        if ($revise->trashed()) $this->error("[$revise->number] has trashed. Not Allowed to REVISION!");
        if ($revise->is_relationship) $this->error("[$revise->number] has relationship. Not Allowed to REVISION!");

        $revise = WorkOrder::findOrFail($id);
        foreach ($revise->work_order_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->work_order_item_lines()->delete();
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
            $work_order->save();

            $work_order->moveState('OPEN');
        }

        $rows = $request->work_order_items ?? [];

        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);

            $detail->work_order_item_lines()->forceDelete();
            $detail->forceDelete();
        }

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $detail = $work_order->work_order_items()->create($row);

            ## Calculate stock on after Detail item updated!
            $FROM = $work_order->stockist_from;
            $detail->item->transfer($detail, $detail->unit_process, 'WIP', $FROM);

            $row_lines = $row['work_order_item_lines'] ?? [];
            for ($j=0; $j < count($row_lines); $j++) {
                $row_line = $row_lines[$j];
                $detail->work_order_item_lines()->create($row_line);
            }
        }

        $revise->moveState('REVISED');
        $revise->revise_id = $work_order->id;
        $revise->save();
        $revise->delete();

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function reopen(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if($work_order->trashed()) $this->error("WO [#$work_order->number] has trashed. Not allowed to be PRODUCTED!");
        if($work_order->status == 'OPEN') $this->error("WO [#$work_order->number] has state 'OPEN'. Not allowed to be PRODUCTED!");

        $FROM = $work_order->stockist_from;
        $work_order->work_order_items->each(function($detail) use ($FROM) {
            $detail->item->distransfer($detail);
            $detail->item->transfer($detail, $detail->unit_amount, 'WO');
        });

        $work_order->stateable()->delete();
        $work_order->moveState('OPEN');

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function producted(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if($work_order->trashed()) $this->error("WO [#$work_order->number] has trashed. Not allowed to be PRODUCTED!");
        if($work_order->status !== 'OPEN') $this->error("WO [#$work_order->number] has state $work_order->status. Not allowed to be PRODUCTED!");
        if($work_order->total_production <= 0) $this->error("WO [#$work_order->number] has not Production. Not allowed to be PRODUCTED!");

        $this->stockRestore($work_order);

        $work_order->moveState('PRODUCTED');

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function Packed(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if($work_order->trashed()) $this->error("WO [#$work_order->number] has trashed. Not allowed to be PACKED!");
        if($work_order->status !== 'PRODUCTED') $this->error("WO [#$work_order->number] has state $work_order->status. Not allowed to be PACKED!");
        if(round($work_order->total_production) != round($work_order->total_packing)) $this->error("WO [#$work_order->number] Total Packing not valid. Not allowed to be PACKED!");

        $work_order->moveState('PACKED');

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function closed(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if ($work_order->trashed()) $this->error("[$work_order->number] has trashed. Not Allowed to be CLOSED!");
        if ($work_order->status == 'CLOSED') $this->error("[$work_order->number] has CLOSED state. Not Allowed to be CLOSED!");
        if($work_order->total_production <= 0) $this->error("WO [#$work_order->number] has not Production. Not allowed to be CLOSED!");
        if(round($work_order->total_production) != round($work_order->total_packing)) $this->error("WO [#$work_order->number] Total Packing not valid. Not allowed to be CLOSED!");

        if ($work_order->status == 'OPEN') $this->stockRestore($work_order);

        if (!$work_order->has_producted) {
            $work_order->moveState('PRODUCTED');
        }

        if (!$work_order->has_packed) {
            $work_order->moveState('PACKED');
        }

        $work_order->moveState('CLOSED');

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function revisionWithRelation(Request $request, $id)
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
            $work_order->save();

            $work_order->moveState($revise->status);
        }

        $rows = $request->work_order_items ?? [];

        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);

            $detail->work_order_item_lines()->forceDelete();
            $detail->forceDelete();
        }

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $detail = $work_order->work_order_items()->create($row);
            $detail->process = $row['process'];
            $detail->save();
            ## Calculate stock on after Detail item updated!
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
                $NC = (double) $packing_item->packing_item_faults()->sum('quantity');
                if ($NC > 0) $packing_item->item->transfer($packing_item, $NC, 'NC', 'WIP');
                $packing_item->amount_faulty = $NC * $packing_item->unit_rate;
                $packing_item->save();

                $packing_item->work_order_item()->associate($detail);
                $packing_item->save();
            }

            $detail->calculate();
        }

        ## Delete [soft] relation when nnnnot has relation!
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

        $revise->moveState('REVISED');
        $revise->revise_id = $work_order->id;
        $revise->save();
        $revise->delete();

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    protected function stockRestore($work_order) {
        foreach ($work_order->work_order_items as  $detail) {
            ## Calculate Over Stock Quantity at item processed!
            $amount_process = round($detail->amount_process);
            $unit_amount = round($detail->unit_amount);
            if ($amount_process < $unit_amount) {
                $OVER = ($unit_amount - $amount_process);
                $detail->item->transfer($detail, $OVER, null, 'WO');
            }
        }
    }
}
