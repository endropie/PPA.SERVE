<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\Transport as Request;
use App\Http\Controllers\ApiController;

use App\Models\Warehouse\Transport;

class Transports extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $transports = Transport::filterable()->get();    
                break;

            case 'datagrid':
                $transports = Transport::filterable()->get();
                
                break;

            default:
                $transports = Transport::collect();                
                break;
        }

        return response()->json($transports);
    }

    public function store(Request $request)
    {
        $transport = Transport::create($request->all());

        return response()->json($transport);
    }

    public function show($id)
    {
        $transport = Transport::findOrFail($id);
        $transport->is_editable = (!$transport->is_related);

        return response()->json($transport);
    }

    public function update(Request $request, $id)
    {
        $transport = Transport::findOrFail($id);

        $transport->update($request->input());

        return response()->json($transport);
    }

    public function destroy($id)
    {
        $transport = Transport::findOrFail($id);
        $transport->delete();

        return response()->json(['success' => true]);
    }
}
