<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\FinishedGood as Request;
use App\Http\Controllers\ApiController;

use App\Models\Warehouse\FinishedGood; 

class FinishedGoods extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $finished_goods = FinishedGood::filterable()->get();    
                break;

            case 'datagrid':    
                $finished_goods = FinishedGood::with(['customer'])->filterable()->get();
                
                break;

            default:
                $finished_goods = FinishedGood::collect();                
                break;
        }

        return response()->json($finished_goods);
    }

    public function store(Request $request)
    {
        $finished_good = FinishedGood::create($request->all());

        $item = $request->finished_good_items;
        for ($i=0; $i < sizeof($item); $i++) { 

            // create item production on the incoming Goods updated!
            $finished_good->finished_good_items()->create($item[$i]);
        }

        return response()->json($finished_good);
    }

    public function show($id)
    {
        $finished_good = FinishedGood::with(['finished_good_items'])->findOrFail($id);
        $finished_good->is_editable = (!$finished_good->is_related);

        return response()->json($finished_good);
    }

    public function update(Request $request, $id)
    {
        $finished_good = FinishedGood::findOrFail($id);

        $finished_good->update($request->input());

        // Delete items on the incoming goods updated!
        $finished_good->finished_good_items()->delete();

        $item = $request->finished_good_items;
        for ($i=0; $i < sizeof($item); $i++) { 

            // create item row on the incoming Goods updated!
            $finished_good->finished_good_items()->create($item[$i]);
        }

        return response()->json($finished_good);
    }

    public function destroy($id)
    {
        $finished_good = FinishedGood::findOrFail($id);
        $finished_good->delete();

        return response()->json(['success' => true]);
    }
}
