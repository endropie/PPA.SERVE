<?php

namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\Vehicle as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Vehicle;

class Vehicles extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $vehicles = Vehicle::filterable()->get();    
                break;

            case 'datagrid':
                $vehicles = Vehicle::filterable()->get();
                
                break;

            default:
                $vehicles = Vehicle::collect();                
                break;
        }

        return response()->json($vehicles);
    }

    public function store(Request $request)
    {
        $vehicle = Vehicle::create($request->all());

        return response()->json($vehicle);
    }

    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->is_editable = (!$vehicle->is_related);

        return response()->json($vehicle);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $vehicle->update($request->input());

        return response()->json($vehicle);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return response()->json(['success' => true]);
    }
}
