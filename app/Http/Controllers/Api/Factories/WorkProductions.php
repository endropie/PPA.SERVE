<?php
namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\WorkProduction as Filters;
use App\Http\Requests\Factory\WorkProduction as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\WorkOrderItem;
use App\Models\Factory\WorkProduction;
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
                $work_productions = WorkProduction::with(['created_user', 'line', 'shift'])
                    ->filter($filter)->latest()->collect();

                $work_productions->getCollection()->transform(function($row) {
                        $row->append(['is_relationship']);
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
        $number =  $this->getNextWorkProductionNumber();

        while ($multiple > 0) {

            $request->merge(['number'=> $number . ($request->input('isMultiple', false) ? ".$multiple" : "")]);

            $work_production = WorkProduction::create($request->all());

            $rows = $request->work_production_items;


            for ($i=0; $i < count($rows); $i++) {
                $row = $rows[$i];
                ## create Part item on the WIP Created!
                $detail = $work_production->work_production_items()->create($row);

                $work_order_item = WorkOrderItem::find($row['work_order_item_id']);

                if(!$work_order_item) $this->error("SPK ITEM [#". $row['work_order_item_id'] ."] has not found. Not Allowed to be SPK CREATED!");

                if ($work_order_item->work_order->status == 'CLOSED') {
                    $this->error("[". $work_order_item->work_order->number ."] has CLOSED state. Not Allowed to be CREATED!");
                }

                if ($work_order_item->work_order->has_producted) {
                    $this->error("[". $work_order_item->work_order->number ."] has PRODUCTED state. Not Allowed to be CREATED!");
                }

                $detail->work_order_item()->associate($work_order_item);
                $detail->save();

                $work_order_item->calculate();
                if (!$work_order_item->work_order->main_id) {
                    $FROM = $work_order_item->work_order->stockist_from;
                    $detail->item->transfer($detail, $detail->unit_amount,'WIP', $FROM);
                    $work_order_item->calculate();

                    $detail->item->refresh();
                    if (round($detail->item->totals[$FROM]) < 0) $this->error("Stock [". $detail->item->part_name ."] invalid. Not Allowed to be CREATED!");
                }

                if ($work_production->isExistFullnumber()) $this->error('GENERATE NUMBER CONFLICT!');

                $detail->work_order_item->setCommentLog("Production [$work_production->fullnumber] has been Created. SPK Detail[#". $work_order_item->id ."] Part ". $detail->item->part_name .".");
            }

            $work_production->setCommentLog("Production [$work_production->fullnumber] has been Created");

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


        $work_production->append(['is_relationship', 'has_relationship']);


        return response()->json($work_production);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $work_production = WorkProduction::findOrFail($id);

        if ($work_production->trashed()) $this->error("[$work_production->number] has trashed. Not Allowed to be UPDATED!");
        if ($work_production->is_relationship) $this->error("[$work_production->number] has relationship. Not Allowed to be UPDATED!");

        foreach ($work_production->work_production_items as $detail) {

            $detail->item->distransfer($detail);

            if($detail->work_order_item) $detail->work_order_item->calculate(false);

            $detail->work_order_item->setCommentLog("Production [$work_production->fullnumber] has been updated (remove row). SPK Detail[#". $detail->work_order_item->id ."] Part ". $detail->item->part_name .".");

            $detail->forceDelete();
        }

        $work_production->update($request->input());

        $rows = $request->work_production_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            ## create Part item on the WIP Created!
            $detail = $work_production->work_production_items()->create($row);

            if($work_order_item = WorkOrderItem::find($row['work_order_item_id'])) {

                if($work_order = $work_order_item->work_order) {
                    if ($work_order->status == 'CLOSED') {
                        $this->error("[$work_order->number] has CLOSED state. Not Allowed to be UPDATED!");
                    }
                    if ($work_order->has_producted) {
                        $this->error("[$work_order->number] has PRODUCTED state. Not Allowed to be UPDATED!");
                    }
                }

                $detail->work_order_item()->associate($work_order_item);
                $detail->save();

                $work_order_item->calculate();
                if (!$work_order_item->work_order->main_id) {
                    $FROM = $work_order_item->work_order->stockist_from;
                    $detail->item->transfer($detail, $detail->unit_amount,'WIP', $FROM);
                    $work_order_item->calculate();

                    $detail->item->refresh();
                    if (round($detail->item->totals[$FROM]) < 0) $this->error("Stock [". $detail->item->part_name ."] invalid. Not Allowed to be CREATED!");
                }

                $work_order_item->setCommentLog("Production [$work_production->fullnumber] has been Updated (add row). SPK Detail[#". $work_order_item->id ."] Part ". $work_order_item->item->part_name .".");
            }
        }

        if ($work_production->isExistFullnumber()) $this->error('GENERATE NUMBER CONFLICT!');

        $work_production->setCommentLog("Production [$work_production->fullnumber] has been UPDATED.");

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

        foreach ($work_production->work_production_items as $detail) {

            $work_order_item = $detail->work_order_item;

            if (!$work_order_item) $this->error("Production $mode INVALID. Detail(#$detail->id) [". $detail->item->part_name ."] undefined!");

            $detail->item->distransfer($detail);
            $detail->work_order_item()->associate(null);
            $detail->save();
            $detail->delete();

            $work_order_item->calculate();
            $work_order_item->setCommentLog("Production [$work_production->fullnumber] has been $mode. SPK Detail[#$work_order_item->id] Part ". $work_order_item->item->part_name .".");

        }

        $work_production->delete();

        $work_production->setCommentLog("Production [$work_production->fullnumber] has been $mode.");

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
