<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Size as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Size;

class Sizes extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $sizes = Size::filter($filter)->get();
                break;

            case 'datagrid':
                $sizes = Size::filter($filter)->get();

                break;

            default:
                $sizes = Size::filter($filter)->collect();
                break;
        }

        return response()->json($sizes);
    }

    public function store(Request $request)
    {
        $size = Size::create($request->all());

        return response()->json($size);
    }

    public function show($id)
    {
        $size = Size::findOrFail($id);
        $size->append(['has_relationship']);

        return response()->json($size);
    }

    public function update(Request $request, $id)
    {
        $size = Size::findOrFail($id);

        $size->update($request->input());

        return response()->json($size);
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return response()->json(['success' => true]);
    }
}
