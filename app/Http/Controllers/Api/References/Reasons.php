<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Reason as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Reason;

class Reasons extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $colors = Reason::filter($filter)->get();
                break;

            case 'datagrid':
                $colors = Reason::filter($filter)->get();

                break;

            default:
                $colors = Reason::filter($filter)->collect();
                break;
        }

        return response()->json($colors);
    }

    public function store(Request $request)
    {
        $color = Reason::create($request->all());

        return response()->json($color);
    }

    public function show($id)
    {
        $color = Reason::findOrFail($id);
        $color->append(['has_relationship']);

        return response()->json($color);
    }

    public function update(Request $request, $id)
    {
        $color = Reason::findOrFail($id);

        $color->update($request->input());

        return response()->json($color);
    }

    public function destroy($id)
    {
        $color = Reason::findOrFail($id);
        $color->delete();

        return response()->json(['success' => true]);
    }
}
