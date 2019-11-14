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

                $work_productions->getCollection()->transform(function($row) {
                        $row->setAppends(['is_relationship']);
                        return $row;
                    });
                break;
        }

        return response()->json($work_productions);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        $multiple = $request->input('isMultiple', false) ? $request->input('multiple', 1) : 1;
        $works = collect();

        while ($multiple > 0) {

            $number =  $this->getNextWorkProductionNumber();
            $request->merge(['number'=> $number]);

            $work_production = WorkProduction::create($request->all());

            $rows = $request->work_production_items;
            for ($i=0; $i < count($rows); $i++) {
                $row = $rows[$i];
                // create Part item on the WIP Created!
                $detail = $work_production->work_production_items()->create($row);

                if($line = WorkOrderItemLine::find($row['work_order_item_line_id'])) {

                    if($work_order = $line->work_order_item->work_order) {
                        if ($work_order->status == 'CLOSED') {
                            $this->error("[$work_order->number] has CLOSED state. Not Allowed to be CREATED!");
                        }
                        if ($work_order->has_producted) {
                            $this->error("[$work_order->number] has PRODUCTED state. Not Allowed to be CREATED!");
                        }
                    }

                    $detail->work_order_item_line()->associate($line);
                    $detail->save();

                    if ($line->ismain) {
                        $detail->item->transfer($detail, $detail->unit_amount,'WIP', 'WO');
                        $line->calculate();
                        $line->work_order_item->calculate();
                    }
                }
            }

            $works->push($work_production);
            $multiple--;
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


        $work_production->setAppends(['is_relationship', 'has_relationship']);


        return response()->json($work_production);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_production = WorkProduction::findOrFail($id);

        if ($work_production->trashed()) $this->error("[$work_production->number] has trashed. Not Allowed to be UPDATED!");
        if ($work_production->is_relationship) $this->error("[$work_production->number] has relationship. Not Allowed to be UPDATED!");

        $work_production->work_production_items->each( function ($detail) {

            $detail->item->distransfer($detail);

            if($line = $detail->work_order_item_line) {
                $line->work_order_item->calculate();
            }
            $detail->forceDelete();
        });

        $work_production->update($request->input());

        $rows = $request->work_production_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // create Part item on the WIP Created!
            $detail = $work_production->work_production_items()->create($row);

            if($line = WorkOrderItemLine::find($row['work_order_item_line_id'])) {
                $detail->work_order_item_line()->associate($line);
                $detail->save();

                if ($line->ismain) {
                    $detail->item->transfer($detail, $detail->unit_amount,'WIP', 'WO');
                    $line->calculate();
                    $line->work_order_item->calculate();
                }
            }
        }

        $this->DATABASE::commit();
        return response()->json($work_production);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $mode = strtoupper(request('mode', 'DELETED'));
        $work_production = WorkProduction::findOrFail($id);

        if ($work_production->trashed()) $this->error("[$work_production->number] has trashed. Not Allowed to be $mode!");
        if ($work_production->is_relationship) $this->error("[$work_production->number] has relationship. Not Allowed to be $mode!");

        $work_production->status = $mode;
        $work_production->save();

        $work_production->work_production_items->each( function ($detail) {

            $line = $detail->work_order_item_line;

            $detail->item->distransfer($detail);
            $detail->work_order_item_line()->associate(null);
            $detail->save();
            $detail->delete();

            if ($line) {
                $line->calculate();
                $line->work_order_item->calculate();
            }
        });

        $work_production->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
