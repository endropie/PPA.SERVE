<?php
namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\WorkProduction as Filters;
use App\Http\Requests\Factory\WorkProduction as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\WorkProduction;
use App\Models\Factory\WorkOrderItemLine;
use App\Traits\GenerateNumber;

class WorkProductions extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filter)
    {
        switch (request('mode')) {
            case 'all':
                $work_productions = WorkProduction::filter($filter)->get();
                break;

            case 'datagrid':
                $work_productions = WorkProduction::with(['line', 'shift'])
                    ->filter($filter)->latest()->get();
                break;

            default:
                $work_productions = WorkProduction::with(['line', 'shift'])
                    ->filter($filter)->latest()->collect();
                break;
        }

        return response()->json($work_productions);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextWorkProductionNumber()]);

        $work_production = WorkProduction::create($request->all());

        $rows = $request->work_production_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // create Part item on the WIP Created!
            $detail = $work_production->work_production_items()->create($row);

            if($work_order_item_line = WorkOrderItemLine::find($row['work_order_item_line_id'])) {
                $detail->work_order_item_line()->associate($work_order_item_line);
                $detail->save();
            }

            $work_order_item_line->calculate();
        }

        $this->DATABASE::commit();
        return response()->json($work_production);
    }

    public function show($id)
    {
        $work_production = WorkProduction::with([
            'line', 'shift',
            'work_production_items.item.item_units',
            'work_production_items.unit'
        ])->withTrashed()->findOrFail($id);
        $work_production->is_editable = (!$work_production->is_related);

        return response()->json($work_production);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_production = WorkProduction::findOrFail($id);

        $work_production->work_production_items->each( function ($detail) {
            $work_order_item_line = $detail->work_order_item_line;
            // $this->error($detail->item);
            $detail->item->distransfer($detail);
            $work_order_item_line->calculate();
            $detail->forceDelete();
        });


        $work_production->update($request->input());

        $rows = $request->work_production_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // create Part item on the WIP Created!
            $detail = $work_production->work_production_items()->create($row);

            if($work_order_item_line = WorkOrderItemLine::find($row['work_order_item_line_id'])) {
                $detail->work_order_item_line()->associate($work_order_item_line);
                $detail->save();
            }

            $work_order_item_line->calculate();
        }

        // $this->error('LOLOS');

        $this->DATABASE::commit();
        return response()->json($work_production);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $work_production = WorkProduction::findOrFail($id);
        $work_production->work_production_items()->delete();
        $work_production->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
