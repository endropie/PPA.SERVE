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
                $faults = Fault::filter($filter)->latest()->get();
                break;

            case 'datagrid':
                $faults = Fault::with('type_fault')->filter($filter)->latest()->get();
                $faults->each->append(['is_relationship']);
                break;

            default:
                $faults = Fault::with('type_fault')->filter($filter)->latest()->collect();
                $faults->getCollection()->transform(function($item) {
                    $item->append(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($faults);
    }

    public function store(Request $request)
    {
        $faulty = Fault::create($request->all());

        return response()->json($faulty);
    }

    public function show($id)
    {
        $faulty = Fault::with('type_fault')->findOrFail($id);
        $faulty->append(['has_relationship']);

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

        if ($faulty->is_relationship) $this->error( strtoupper($faulty->name). " has relationships. DELETED not allowed!");

        $faulty->delete();
        return response()->json(['success' => true]);
    }
}
