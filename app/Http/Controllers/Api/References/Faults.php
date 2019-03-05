<?php

namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\Fault as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Fault;

class Faults extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $faults = Fault::filterable()->get();    
                break;

            case 'datagrid':
                $faults = Fault::filterable()->get();
                
                break;

            default:
                $faults = Fault::collect();                
                break;
        }

        return response()->json($faults);
    }

    public function store(Request $request)
    {
        $fault = Fault::create($request->all());

        return response()->json($fault);
    }

    public function show($id)
    {
        $fault = Fault::findOrFail($id);
        $fault->is_editable = (!$fault->is_related);

        return response()->json($fault);
    }

    public function update(Request $request, $id)
    {
        $fault = Fault::findOrFail($id);

        $fault->update($request->input());

        return response()->json($fault);
    }

    public function destroy($id)
    {
        $fault = Fault::findOrFail($id);
        $fault->delete();

        return response()->json(['success' => true]);
    }
}
