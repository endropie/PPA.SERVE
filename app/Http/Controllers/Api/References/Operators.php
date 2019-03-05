<?php

namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\Operator as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Operator;

class Operators extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $operators = Operator::filterable()->get();    
                break;

            case 'datagrid':
                $operators = Operator::filterable()->get();
                
                break;

            default:
                $operators = Operator::collect();                
                break;
        }

        return response()->json($operators);
    }

    public function store(Request $request)
    {
        $operator = Operator::create($request->all());

        return response()->json($operator);
    }

    public function show($id)
    {
        $operator = Operator::findOrFail($id);
        $operator->is_editable = (!$operator->is_related);

        return response()->json($operator);
    }

    public function update(Request $request, $id)
    {
        $operator = Operator::findOrFail($id);

        $operator->update($request->input());

        return response()->json($operator);
    }

    public function destroy($id)
    {
        $operator = Operator::findOrFail($id);
        $operator->delete();

        return response()->json(['success' => true]);
    }
}
