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
                $items = Item::with(['item_productions','brand','customer','specification'])->filterable()->get();
                
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

        $pre = $request->item_productions;
        for ($i=0; $i < sizeof($pre); $i++) { 

            // create pre production on the item updated!
            $item->item_productions()->create($pre[$i]);
        }

        return response()->json($item);
    }

    public function show($id)
    {
        $item = Item::with(['item_productions'])->findOrFail($id);
        $item->is_editable = (!$item->is_related);

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $item->update($request->input());

        // Delete pre production on the item updated!
        $item->item_productions()->delete();

        $pre = $request->item_productions;
        for ($i=0; $i < sizeof($pre); $i++) { 

            // create pre production on the item updated!
            $item->item_productions()->create($pre[$i]);
        }

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json(['success' => true]);
    }
}
