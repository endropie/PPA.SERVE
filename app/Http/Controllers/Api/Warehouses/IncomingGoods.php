<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\IncomingGood as Request;
use App\Http\Controllers\ApiController;

use App\Models\Warehouse\IncomingGood; 

class IncomingGoods extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $incoming_goods = IncomingGood::filterable()->get();    
                break;

            case 'datagrid':    
                $incoming_goods = IncomingGood::with(['customer'])->filterable()->get();
                
                break;

            default:
                $incoming_goods = IncomingGood::collect();                
                break;
        }

        return response()->json($incoming_goods);
    }

    public function store(Request $request)
    {
        $incoming_good = IncomingGood::create($request->all());

        $item = $request->incoming_good_items;
        for ($i=0; $i < sizeof($item); $i++) { 

            // create item production on the incoming Goods updated!
            $incoming_good->incoming_good_items()->create($item[$i]);
        }

        return response()->json($incoming_good);
    }

    public function show($id)
    {
        $incoming_good = IncomingGood::with(['incoming_good_items'])->findOrFail($id);
        $incoming_good->is_editable = (!$incoming_good->is_related);

        return response()->json($incoming_good);
    }

    public function update(Request $request, $id)
    {
        $incoming_good = IncomingGood::findOrFail($id);

        $incoming_good->update($request->input());

        // Delete items on the incoming goods updated!
        $incoming_good->incoming_good_items()->delete();

        $item = $request->incoming_good_items;
        for ($i=0; $i < sizeof($item); $i++) { 

            // create item row on the incoming Goods updated!
            $incoming_good->incoming_good_items()->create($item[$i]);
        }

        return response()->json($incoming_good);
    }

    public function destroy($id)
    {
        $incoming_good = IncomingGood::findOrFail($id);
        $incoming_good->delete();

        return response()->json(['success' => true]);
    }
}
