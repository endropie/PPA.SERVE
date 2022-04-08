<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Packarea as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Packarea;

class Packareas extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $packareas = Packarea::filter($filter)->get();
                break;

            case 'datagrid':
                $packareas = Packarea::filter($filter)->get();

                break;

            default:
                $packareas = Packarea::filter($filter)->collect();
                break;
        }

        return response()->json($packareas);
    }

    public function store(Request $request)
    {
        $packarea = Packarea::create($request->all());

        return response()->json($packarea);
    }

    public function show($id)
    {
        $packarea = Packarea::findOrFail($id);
        $packarea->append(['has_relationship']);

        return response()->json($packarea);
    }

    public function update(Request $request, $id)
    {
        $packarea = Packarea::findOrFail($id);

        $packarea->update($request->input());

        return response()->json($packarea);
    }

    public function destroy($id)
    {
        $packarea = Packarea::findOrFail($id);
        $packarea->delete();

        return response()->json(['success' => true]);
    }
}
