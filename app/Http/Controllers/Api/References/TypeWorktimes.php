<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\TypeWorktime as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\TypeWorktime;

class TypeWorktimes extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $type_worktimes = TypeWorktime::filterable()->get();    
                break;

            case 'datagrid':
                $type_worktimes = TypeWorktime::filterable()->get();
                
                break;

            default:
                $type_worktimes = TypeWorktime::collect();                
                break;
        }

        return response()->json($type_worktimes);
    }

    public function store(Request $request)
    {
        $type_worktime = TypeWorktime::create($request->all());

        return response()->json($type_worktime);
    }

    public function show($id)
    {
        $type_worktime = TypeWorktime::findOrFail($id);
        $type_worktime->is_editable = (!$type_worktime->is_related);

        return response()->json($type_worktime);
    }

    public function update(Request $request, $id)
    {
        $type_worktime = TypeWorktime::findOrFail($id);

        $type_worktime->update($request->input());

        return response()->json($type_worktime);
    }

    public function destroy($id)
    {
        $type_worktime = TypeWorktime::findOrFail($id);
        $type_worktime->delete();

        return response()->json(['success' => true]);
    }
}
