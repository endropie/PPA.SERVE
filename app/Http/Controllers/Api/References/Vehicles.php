<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Vehicle as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Vehicle;

class Vehicles extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $vehicles = Vehicle::filter($filter)->get();
                break;

            case 'datagrid':
                $vehicles = Vehicle::with('department')->filter($filter)->get();

                break;

            default:
                $vehicles = Vehicle::with('department')->filter($filter)->collect();
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
        $vehicle->append(['has_relationship']);

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
