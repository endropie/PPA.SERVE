<?php

namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\Line as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Line;

class Lines extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $lines = Line::filterable()->get();    
                break;

            case 'datagrid':
                $lines = Line::filterable()->get();
                
                break;

            default:
                $lines = Line::collect();                
                break;
        }

        return response()->json($lines);
    }

    public function store(Request $request)
    {
        $line = Line::create($request->all());

        return response()->json($line);
    }

    public function show($id)
    {
        $line = Line::findOrFail($id);
        $line->is_editable = (!$line->is_related);

        return response()->json($line);
    }

    public function update(Request $request, $id)
    {
        $line = Line::findOrFail($id);

        $line->update($request->input());

        return response()->json($line);
    }

    public function destroy($id)
    {
        $line = Line::findOrFail($id);
        $line->delete();

        return response()->json(['success' => true]);
    }
}
