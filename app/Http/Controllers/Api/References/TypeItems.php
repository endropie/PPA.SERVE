<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\TypeItem as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\TypeItem;

class TypeItems extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $itemtypes = TypeItem::filterable()->get();    
                break;

            case 'datagrid':
                $itemtypes = TypeItem::filterable()->get();
                
                break;

            default:
                $itemtypes = TypeItem::collect();                
                break;
        }

        return response()->json($itemtypes);
    }

    public function store(Request $request)
    {
        $itemtype = TypeItem::create($request->all());

        return response()->json($itemtype);
    }

    public function show($id)
    {
        $itemtype = TypeItem::findOrFail($id);
        $itemtype->is_editable = (!$itemtype->is_related);

        return response()->json($itemtype);
    }

    public function update(Request $request, $id)
    {
        $itemtype = TypeItem::findOrFail($id);

        $itemtype->update($request->input());

        return response()->json($itemtype);
    }

    public function destroy($id)
    {
        $itemtype = TypeItem::findOrFail($id);
        $itemtype->delete();

        return response()->json(['success' => true]);
    }
}
