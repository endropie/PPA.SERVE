<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Color as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Color;

class Colors extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $colors = Color::filter($filter)->get();
                break;

            case 'datagrid':
                $colors = Color::filter($filter)->get();

                break;

            default:
                $colors = Color::filter($filter)->collect();
                break;
        }

        return response()->json($colors);
    }

    public function store(Request $request)
    {
        $color = Color::create($request->all());

        return response()->json($color);
    }

    public function show($id)
    {
        $color = Color::findOrFail($id);
        $color->append(['has_relationship']);

        return response()->json($color);
    }

    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);

        $color->update($request->input());

        return response()->json($color);
    }

    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return response()->json(['success' => true]);
    }
}
