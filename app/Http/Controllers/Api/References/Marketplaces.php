<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\Marketplace as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Marketplace;

class Marketplaces extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $marketplaces = Marketplace::filterable()->get();
                break;

            case 'datagrid':
                $marketplaces = Marketplace::filterable()->get();
                break;

            default:
                $marketplaces = Marketplace::collect();                
                break;
        }

        return response()->json($marketplaces);
    }

    public function store(Request $request)
    {
        $marketplace = Marketplace::create($request->all());

        return response()->json($marketplace);
    }

    public function show($id)
    {
        $marketplace = Marketplace::findOrFail($id);
        $marketplace->is_editable = (!$marketplace->is_related);

        return response()->json($marketplace);
    }

    public function update(Request $request, $id)
    {
        $marketplace = Marketplace::findOrFail($id);

        $marketplace->update($request->input());

        return response()->json($marketplace);
    }

    public function destroy($id)
    {
        //
    }
}
