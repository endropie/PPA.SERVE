<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\Unit as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Unit;

class Units extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $units = Unit::filterable()->get();    
                break;

            case 'datagrid':
                $units = Unit::filterable()->get();
                
                break;

            default:
                $units = Unit::collect();                
                break;
        }

        return response()->json($units);
    }

    public function store(Request $request)
    {
        $unit = Unit::create($request->all());

        return response()->json($unit);
    }

    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->is_editable = (!$unit->is_related);

        return response()->json($unit);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $unit->update($request->input());

        return response()->json($unit);
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json(['success' => true]);
    }
}
