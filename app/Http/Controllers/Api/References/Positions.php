<?php
namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\Position as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter as Filter;
use App\Models\Reference\Position;

class Positions extends ApiController
{
    public function index(Filter $filters)
    {
        switch (request('mode')) {
            case 'all':
                $positions = Position::filter($filters)->get();
                break;

            case 'datagrid':
                $positions = Position::filter($filters)->get();
                break;

            default:
                $positions = Position::filter($filters)->collect();
                break;
        }

        return response()->json($positions);
    }

    public function store(Request $request)
    {
        $position = Position::create($request->all());

        return response()->json($position);
    }

    public function show($id)
    {
        $position = Position::findOrFail($id);
        $position->append(['has_relationship']);

        return response()->json($position);
    }

    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $position->update($request->input());

        return response()->json($position);
    }

    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        $position->delete();

        return response()->json(['success' => true]);
    }
}
