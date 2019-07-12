<?php
namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\WorkinProduction as Filters;
use App\Http\Requests\Factory\WorkinProduction as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\WorkinProduction; 
use App\Models\Factory\WorkOrderItem;
use App\Traits\GenerateNumber;

class WorkinProductions extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filter)
    {
        switch (request('mode')) {
            case 'all':            
                $workin_productions = WorkinProduction::filter($filter)->get();    
                break;

            case 'datagrid':    
                $workin_productions = WorkinProduction::with(['line', 'shift', 'workin_production_items'])
                    ->filter($filter)->get();
                break;

            default:
                $workin_productions = WorkinProduction::with(['line', 'shift', 'workin_production_items'])
                    ->filter($filter)->collect();                
                break;
        }

        return response()->json($workin_productions);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        
        if(!$request->number) $request->merge(['number'=> $this->getNextWorkinProductionNumber()]);

        $workin_production = WorkinProduction::create($request->all());

        $rows = $request->workin_production_items;
        for ($i=0; $i < count($rows); $i++) { 
            $row = $rows[$i];
            // create Part item on the WIP Created!
            $detail = $workin_production->workin_production_items()->create($row);

            if($work_order_item = WorkOrderItem::find($row['work_order_item_id'])) {
                $detail->work_order_item()->associate($work_order_item);
                $detail->save();
            }
        }

        $this->DATABASE::commit();
        return response()->json($workin_production);
    }

    public function show($id)
    {
        $workin_production = WorkinProduction::with([
            'line',
            'workin_production_items.item.item_units', 
            'workin_production_items.unit' 
        ])->findOrFail($id);
        $workin_production->is_editable = (!$workin_production->is_related);

        return response()->json($workin_production);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $workin_production = WorkinProduction::findOrFail($id);

        $workin_production->update($request->input());

        $rows = $request->workin_production_items;
        $deletes = $workin_production->workin_production_items()->whereNotIn('id', array_filter(array_column($rows, 'id')))->delete();

        for ($i=0; $i < count($rows); $i++) { 
            $row = $rows[$i];
            // create item row on the WIP updated!
            $detail = $workin_production->workin_production_items()->updateOrCreate(['id' => $row['id']], $row);

            if($work_order_item = WorkOrderItem::find($row['work_order_item_id'])) {
                $detail->work_order_item()->associate($work_order_item);
                $detail->save();
            }
        }

        $this->DATABASE::commit();
        return response()->json($workin_production);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $workin_production = WorkinProduction::findOrFail($id);
        $workin_production->workin_production_items()->delete();
        $workin_production->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
