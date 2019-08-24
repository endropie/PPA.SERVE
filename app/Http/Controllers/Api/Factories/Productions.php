<?php
namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\Production as Filters;
use App\Http\Requests\Factory\Production as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\Production;
use App\Models\Factory\WorkOrderItem;
use App\Traits\GenerateNumber;

class Productions extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filter)
    {
        switch (request('mode')) {
            case 'all':
                $productions = Production::filter($filter)->get();
                break;

            case 'datagrid':
                $productions = Production::with(['line', 'shift', 'production_items'])
                    ->filter($filter)->get();
                break;

            default:
                $productions = Production::with(['line', 'shift', 'production_items'])
                    ->filter($filter)->collect();
                break;
        }

        return response()->json($productions);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextWorkProductionNumber()]);

        $production = Production::create($request->all());

        $rows = $request->production_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // create Part item on the WIP Created!
            $detail = $production->production_items()->create($row);

            if($work_order_item = WorkOrderItem::find($row['work_order_item_id'])) {
                $detail->work_order_item()->associate($work_order_item);
                $detail->save();
            }
        }

        $this->DATABASE::commit();
        return response()->json($production);
    }

    public function show($id)
    {
        $production = Production::with([
            'line',
            'production_items.item.item_units',
            'production_items.unit'
        ])->withTrashed()->findOrFail($id);
        $production->is_editable = (!$production->is_related);

        return response()->json($production);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $production = Production::findOrFail($id);

        $production->update($request->input());

        $rows = $request->production_items;
        $deletes = $production->production_items()->whereNotIn('id', array_filter(array_column($rows, 'id')))->delete();

        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // create item row on the WIP updated!
            $detail = $production->production_items()->updateOrCreate(['id' => $row['id']], $row);

            if($work_order_item = WorkOrderItem::find($row['work_order_item_id'])) {
                $detail->work_order_item()->associate($work_order_item);
                $detail->save();
            }
        }

        $this->DATABASE::commit();
        return response()->json($production);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $production = Production::findOrFail($id);
        $production->production_items()->delete();
        $production->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
