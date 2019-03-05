<?php

namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\TypeFault as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\TypeFault;

class TypeFaults extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $type_faults = TypeFault::filterable()->get();    
                break;

            case 'datagrid':
                $type_faults = TypeFault::filterable()->get();
                
                break;

            default:
                $type_faults = TypeFault::collect();                
                break;
        }

        return response()->json($type_faults);
    }

    public function store(Request $request)
    {
        $type_fault = TypeFault::create($request->all());

        return response()->json($type_fault);
    }

    public function show($id)
    {
        $type_fault = TypeFault::findOrFail($id);
        $type_fault->is_editable = (!$type_fault->is_related);

        return response()->json($type_fault);
    }

    public function update(Request $request, $id)
    {
        $type_fault = TypeFault::findOrFail($id);

        $type_fault->update($request->input());

        return response()->json($type_fault);
    }

    public function destroy($id)
    {
        $type_fault = TypeFault::findOrFail($id);
        $type_fault->delete();

        return response()->json(['success' => true]);
    }
}
