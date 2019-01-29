<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\Ordertype as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Ordertype;

class Ordertypes extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $ordertypes = Ordertype::filterable()->get();    
                break;

            case 'datagrid':
                $ordertypes = Ordertype::filterable()->get();
                
                break;

            default:
                $ordertypes = Ordertype::collect();                
                break;
        }

        return response()->json($ordertypes);
    }

    public function store(Request $request)
    {
        $ordertype = Ordertype::create($request->all());

        return response()->json($ordertype);
    }

    public function show($id)
    {
        $ordertype = Ordertype::findOrFail($id);
        $ordertype->is_editable = (!$ordertype->is_related);

        return response()->json($ordertype);
    }

    public function update(Request $request, $id)
    {
        $ordertype = Ordertype::findOrFail($id);

        $ordertype->update($request->input());

        return response()->json($ordertype);
    }

    public function destroy($id)
    {
        $ordertype = Ordertype::findOrFail($id);
        $ordertype->delete();

        return response()->json(['success' => true]);
    }
}
