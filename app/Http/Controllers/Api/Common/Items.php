<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Requests\Common\Item as Request;
use App\Http\Controllers\ApiController;

use App\Models\Common\Item; 

class Items extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $items = Item::filterable()->get();    
                break;

            case 'datagrid':
                $items = Item::with(['color'])->filterable()->get();
                
                break;

            default:
                $items = Item::collect();                
                break;
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $item = Item::create($request->all());

        return response()->json($item);
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);
        $item->is_editable = (!$item->is_related);

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $item->update($request->input());

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json(['success' => true]);
    }
}
