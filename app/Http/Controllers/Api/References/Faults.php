<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Fault as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Fault;

class Faults extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $faulties = Fault::filter($filter)->get();
                break;

            case 'datagrid':
                $faulties = Fault::filter($filter)->get();

                break;

            default:
                $faulties = Fault::filter($filter)->collect();
                break;
        }

        return response()->json($faulties);
    }

    public function store(Request $request)
    {
        $faulty = Fault::create($request->all());

        return response()->json($faulty);
    }

    public function show($id)
    {
        $faulty = Fault::findOrFail($id);
        $faulty->setAppends(['has_relationship']);

        return response()->json($faulty);
    }

    public function update(Request $request, $id)
    {
        $faulty = Fault::findOrFail($id);

        $faulty->update($request->input());

        return response()->json($faulty);
    }

    public function destroy($id)
    {
        $faulty = Fault::findOrFail($id);
        $faulty->delete();

        return response()->json(['success' => true]);
    }
}
