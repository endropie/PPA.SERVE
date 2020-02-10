<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Line as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Line;

class Lines extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $lines = Line::filter($filter)->get();
                break;

            case 'datagrid':
                $lines = Line::filter($filter)->get();

                break;

            default:
                $lines = Line::filter($filter)->collect();
                break;
        }

        return response()->json($lines);
    }

    public function store(Request $request)
    {
        $line = Line::create($request->all());

        return response()->json($line);
    }

    public function show($id)
    {
        $line = Line::findOrFail($id);
        $line->append(['has_relationship']);

        return response()->json($line);
    }

    public function update(Request $request, $id)
    {
        $line = Line::findOrFail($id);

        $line->update($request->input());

        return response()->json($line);
    }

    public function destroy($id)
    {
        $line = Line::findOrFail($id);
        $line->delete();

        return response()->json(['success' => true]);
    }
}
