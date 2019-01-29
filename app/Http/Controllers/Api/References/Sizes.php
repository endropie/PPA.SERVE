<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\Size as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Size;

class Sizes extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $sizes = Size::filterable()->get();    
                break;

            case 'datagrid':
                $sizes = Size::filterable()->get();
                
                break;

            default:
                $sizes = Size::collect();                
                break;
        }

        return response()->json($sizes);
    }

    public function store(Request $request)
    {
        $size = Size::create($request->all());

        return response()->json($size);
    }

    public function show($id)
    {
        $size = Size::findOrFail($id);
        $size->is_editable = (!$size->is_related);

        return response()->json($size);
    }

    public function update(Request $request, $id)
    {
        $size = Size::findOrFail($id);

        $size->update($request->input());

        return response()->json($size);
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return response()->json(['success' => true]);
    }
}
