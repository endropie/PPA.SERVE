<?php

namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\WorkOrder as Filter;
use App\Filters\Factory\WorkOrderItem as FilterItem;
use App\Http\Requests\Factory\WorkOrder as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\WorkOrder;
use App\Models\Factory\WorkOrderItem;
use App\Traits\GenerateNumber;
class WorkOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter, FilterItem $filterItem)
    {
        switch (request('mode')) {
            case 'all':
            $work_orders = WorkOrder::filter($filter)->latest()->get();
            break;

            case 'datagrid':
            $work_orders = WorkOrder::with(['line',
              'work_order_items.item'
            ])->filter($filter)->get();
            break;

            case 'items':
            $work_orders = WorkOrderItem::with(['item','work_order'])->filter($filterItem)->get();
            break;

            default:
                $work_orders = WorkOrder::with(['created_user', 'line', 'shift'])->filter($filter)->latest()->collect();
                $work_orders->getCollection()->transform(function($row) {
                    $row->append(['is_relationship', 'has_producted', 'has_packed', 'summary_items', 'summary_productions', 'summary_packings']);
                    return $row;
                });

                break;
        }

        return response()->json($work_orders);
    }

    public function items(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
            $work_order_items = WorkOrderItem::filter($filter)->latest()->get();
            break;

            default:
                $work_order_items = WorkOrderItem::filter($filter)->latest()->collect();

                $work_order_items->getCollection()->transform(function($row) {
                    return $row;
                });

                break;
        }

        return response()->json($work_order_items);
    }

    public function hangerLines (Filter $filter)
    {
        if (!request('date')) return $this->error('REQUEST DATE REQUIRED');

        $work_order_lines = WorkOrder::with(['line', 'shift'])
            ->filter($filter)->get();

            $work_order_lines = $work_order_lines
                ->groupBy(function($item, $key){ return $item["line_id"]."-".$item["shift_id"]; })
                ->values()
                ->map(function ($rows) {
                    return array_merge($rows->first()->toArray(), [
                        "hanger_amount" => $rows->sum('hanger_amount'),
                        "hanger_production" => $rows->sum('hanger_production'),
                        "hanger_packing" => $rows->sum('hanger_packing')
                        ]);

                })
                ->sortBy(function ($item) { return $item['shift_id'] ."-". $item['line_id']; })
                ->values();

        return response()->json($work_order_lines);
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

            if (!$work_order->stockist_direct) {
                $FROM = $work_order->stockist_from;
                $detail->item->transfer($detail, $detail->unit_amount, 'WO'.$FROM);
            }
        }

        if (!$work_order->stockist_direct) {
            $this->storeSublines($work_order);
        }

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function show($id)
    {
        switch (request('mode')) {
            case 'prelines':
                $with = [];
                break;

            default:
                $with = [
                    'work_order_items.work_production_items.work_production',
                    'work_order_items.packing_item_orders.packing_item.packing',
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

        $work_order->append(['is_relationship', 'has_relationship', 'has_producted', 'has_packed']);

        return response()->json($work_order);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') == 'revision') return $this->error('NOT SUPPORTED'); //$this->revision($request, $id);
        if(request('mode') == 'producted') return $this->producted($request, $id);
        if(request('mode') == 'packed') return $this->packed($request, $id);
        if(request('mode') == 'closed') return $this->closed($request, $id);
        if(request('mode') == 'reopen') return $this->reopen($request, $id);
        if(request('mode') == 'directed') return $this->directValidated($request, $id);

        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if($work_order->is_relationship) $this->error("[$work_order->number] has RELATIONSHIP, is not allowed to be Updated!");
        if($work_order->status != "OPEN") $this->error("[$work_order->number] not OPEN state, is not allowed to be Updated!");

        $work_order->update($request->input());

        $rows = $request->work_order_items;

        foreach ($work_order->work_order_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->forceDelete();
        }

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $detail = $work_order->work_order_items()->create($row);

            if (!$work_order->stockist_direct) {
                ## Calculate stock on after Detail item updated!
                $FROM = $work_order->stockist_from;
                $detail->item->transfer($detail, $detail->unit_amount, 'WO'.$FROM);
            }
        }

        if (!$work_order->stockist_direct) {
            $this->storeSublines($work_order);
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
            $detail->delete();
        }

        foreach ($work_order->sub_work_orders as $sub_work_order) {
            $sub_work_order->work_order_items()->delete();
            $sub_work_order->delete();
        }

        $work_order->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
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
            $detail->item->transfer($detail, $detail->unit_amount, 'WO'.$FROM);
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

        if (!$work_order->main_id)
        {
            if(round($work_order->total_production) != round($work_order->total_packing)) $this->error("WO [#$work_order->number] Total Packing not valid. Not allowed to be CLOSED!");

            if ($work_order->status == 'OPEN') $this->stockRestore($work_order);

            if (!$work_order->has_producted) {
                $work_order->moveState('PRODUCTED');
            }

            if (!$work_order->has_packed) {
                $work_order->moveState('PACKED');
            }
        }

        $work_order->moveState('CLOSED');

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    public function directValidated(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_order = WorkOrder::findOrFail($id);

        if($work_order->trashed()) $this->error("WO [#$work_order->number] has trashed. Not allowed to be VALIDATED!");
        if($work_order->status != 'OPEN') $this->error("WO [#$work_order->number] has state 'OPEN'. Not allowed to be VALIDATED!");

        $FROM = $work_order->stockist_from;
        $DIRECT = $work_order->stockist_direct;
        $work_order->work_order_items->each(function($detail) use ($FROM, $DIRECT) {
            $detail->item->distransfer($detail);
            $detail->item->transfer($detail, $detail->unit_amount, $DIRECT, $FROM);
        });

        if ($user = auth()->user()) {
            $work_order->direct_validated_by = $user->id;
            $work_order->save();
        }

        $work_order->moveState('CLOSED');

        $this->DATABASE::commit();
        return response()->json($work_order);
    }

    protected function stockRestore($work_order)
    {
        foreach ($work_order->work_order_items as  $detail) {
            ## Calculate Over Stock Quantity at item processed!
            $amount_process = round($detail->amount_process);
            $unit_amount = round($detail->unit_amount);
            $FROM = $work_order->stockist_from;
            if ($amount_process < $unit_amount) {
                $OVER = ($unit_amount - $amount_process);
                $detail->item->transfer($detail, $OVER, null, 'WO'.$FROM);
            }
        }
    }

    protected function storeSublines($work_order)
    {
        if ($work_order->sub_work_orders->count())
        {
            ## REMOVE SUB WORK ORDERS
            foreach ($work_order->sub_work_orders as $sub_work_order) {
                foreach ($sub_work_order->work_order_items as $work_order_item) {
                    $work_order_item->item->distransfer($work_order_item);
                    $work_order_item->forceDelete();
                }
                $sub_work_order->forceDelete();
            }
        }

        $work_order->refresh();

        if ($work_order->stockist_direct) return $this->error("STORE SUBLINE FAILED. [WO IS FG DIRECT]");

        foreach ($work_order->work_order_items as $work_order_item) {

            $sublines = $work_order_item->item->item_prelines()->where('ismain', 0)->get();
            foreach ($sublines as $subline) {
                $sub_work_order = WorkOrder::firstOrcreate(['main_id' => $work_order->id, 'line_id' => $subline->line_id],
                    array_merge($work_order->toArray(), [
                        'main_id' => $work_order->id,
                        'line_id' => $subline->line_id,
                        'number' => $work_order->number . "-$subline->line_id",
                    ])
                );
                $sub_work_order_item = $sub_work_order->work_order_items()->create($work_order_item->toArray());
            }

        }

    }
}
