<?php
namespace App\Http\Controllers\Api\Warehouses;

use App\Filters\Filter;
use App\Http\Requests\Warehouse\Transport as Request;
use App\Http\Controllers\ApiController;
use App\Models\Warehouse\Transport;

class Transports extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $transports = Transport::filter($filter)->get();
                break;

            case 'datagrid':
                $transports = Transport::filter($filter)->get();

                break;

            default:
                $transports = Transport::filter($filter)->collect();
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
