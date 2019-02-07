<?php

namespace App\Http\Controllers\Api\Factories;

use App\Http\Requests\Factory\WorkinProcess as Request;
use App\Http\Controllers\ApiController;

use App\Models\Factory\WorkinProcess; 

class WorkinProcesses extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $workin_processes = WorkinProcess::filterable()->get();    
                break;

            case 'datagrid':    
                $workin_processes = WorkinProcess::with(['customer'])->filterable()->get();
                
                break;

            default:
                $workin_processes = WorkinProcess::collect();                
                break;
        }

        return response()->json($workin_processes);
    }

    public function store(Request $request)
    {
        $workin_process = WorkinProcess::create($request->all());

        $item = $request->workin_process_items;
        for ($i=0; $i < sizeof($item); $i++) { 

            // create item production on the incoming Goods updated!
            $workin_process->workin_process_items()->create($item[$i]);
        }

        return response()->json($workin_process);
    }

    public function show($id)
    {
        $workin_process = WorkinProcess::with(['workin_process_items'])->findOrFail($id);
        $workin_process->is_editable = (!$workin_process->is_related);

        return response()->json($workin_process);
    }

    public function update(Request $request, $id)
    {
        $workin_process = WorkinProcess::findOrFail($id);

        $workin_process->update($request->input());

        // Delete items on the incoming goods updated!
        $workin_process->workin_process_items()->delete();

        $item = $request->workin_process_items;
        for ($i=0; $i < sizeof($item); $i++) { 

            // create item row on the incoming Goods updated!
            $workin_process->workin_process_items()->create($item[$i]);
        }

        return response()->json($workin_process);
    }

    public function destroy($id)
    {
        $workin_process = WorkinProcess::findOrFail($id);
        $workin_process->delete();

        return response()->json(['success' => true]);
    }
}
