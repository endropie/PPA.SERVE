<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Unit as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Unit;

class Units extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $units = Unit::filter($filter)->get();
                break;

            case 'datagrid':
                $units = Unit::filter($filter)->get();

                break;

            default:
                $units = Unit::filter($filter)->collect();
                break;
        }

        return response()->json($units);
    }

    public function store(Request $request)
    {

        $input = $request->merge(['code' => strtoupper($request->code)])->input();
        $unit = Unit::create($input);

        return response()->json($unit);
    }

    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->append(['has_relationship']);

        return response()->json($unit);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $input = $request->merge(['code' => strtoupper($request->code)])->input();
        $unit->update($input);

        return response()->json($unit);
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json(['success' => true]);
    }
}
