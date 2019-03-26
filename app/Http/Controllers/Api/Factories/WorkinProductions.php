<?php

namespace App\Http\Controllers\Api\Factories;

use App\Http\Requests\Factory\WorkinProduction as Request;
use App\Http\Controllers\ApiController;

use App\Models\Factory\WorkinProduction; 
use App\Traits\GenerateNumber;

class WorkinProductions extends ApiController
{
    use GenerateNumber;

    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $workin_productions = WorkinProduction::filterable()->get();    
                break;

            case 'datagrid':    
                $workin_productions = WorkinProduction::with(['line', 'shift', 'workin_production_items'])->filterable()->get();
                break;

            default:
                $workin_productions = WorkinProduction::with(['line', 'shift', 'workin_production_items'])->collect();                
                break;
        }

        return response()->json($workin_productions);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        
        if(!$request->number) $request->merge(['number'=> $this->getNextWorkinProductionNumber()]);

        $workin_production = WorkinProduction::create($request->all());

        $item = $request->workin_production_items;
        for ($i=0; $i < count($item); $i++) { 

            // create item production on the incoming Goods updated!
            $workin_production->workin_production_items()->create($item[$i]);
        }

        $this->DATABASE::commit();
        return response()->json($workin_production);
    }

    public function show($id)
    {
        $workin_production = WorkinProduction::with(['workin_production_items.item.item_units', 'workin_production_items.item.unit'])->findOrFail($id);
        $workin_production->is_editable = (!$workin_production->is_related);

        return response()->json($workin_production);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $workin_production = WorkinProduction::findOrFail($id);

        $workin_production->update($request->input());

        // Delete items on the incoming goods updated!
        $workin_production->workin_production_items()->delete();

        $item = $request->workin_production_items;
        for ($i=0; $i < count($item); $i++) { 

            // create item row on the incoming Goods updated!
            $workin_production->workin_production_items()->create($item[$i]);
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
