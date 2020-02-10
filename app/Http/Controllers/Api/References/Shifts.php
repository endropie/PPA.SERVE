<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Shift as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Shift;

class Shifts extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $shifts = Shift::filter($filter)->get();
                break;

            case 'datagrid':
                $shifts = Shift::filter($filter)->get();

                break;

            default:
                $shifts = Shift::filter($filter)->collect();
                break;
        }

        return response()->json($shifts);
    }

    public function store(Request $request)
    {
        $shift = Shift::create($request->all());

        return response()->json($shift);
    }

    public function show($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->append(['has_relationship']);

        return response()->json($shift);
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        $shift->update($request->input());

        return response()->json($shift);
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return response()->json(['success' => true]);
    }
}
