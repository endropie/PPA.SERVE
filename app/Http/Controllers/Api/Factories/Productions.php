<?php

namespace App\Http\Controllers\Api\Factories;

use App\Http\Requests\Factory\Production as Request;
use App\Http\Controllers\ApiController;

use App\Models\Factory\Production;

class Productions extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $productions = Production::filterable()->get();    
                break;

            case 'datagrid':
                $productions = Production::filterable()->get();
                
                break;

            default:
                $productions = Production::collect();                
                break;
        }

        return response()->json($productions);
    }

    public function store(Request $request)
    {
        $production = Production::create($request->all());

        return response()->json($production);
    }

    public function show($id)
    {
        $production = Production::findOrFail($id);
        $production->is_editable = (!$production->is_related);

        return response()->json($production);
    }

    public function update(Request $request, $id)
    {
        $production = Production::findOrFail($id);

        $production->update($request->input());

        return response()->json($production);
    }

    public function destroy($id)
    {
        $production = Production::findOrFail($id);
        $production->delete();

        return response()->json(['success' => true]);
    }
}
