<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\TypeItem as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\TypeItem;

class TypeItems extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $typeItems = TypeItem::filter($filter)->get();
                break;

            case 'datagrid':
                $typeItems = TypeItem::filter($filter)->get();
                break;

            default:
                $typeItems = TypeItem::filter($filter)->collect();
                break;
        }

        return response()->json($typeItems);
    }

    public function store(Request $request)
    {
        $typeItem = TypeItem::create($request->all());

        return response()->json($typeItem);
    }

    public function show($id)
    {
        $typeItem = TypeItem::findOrFail($id);
        $typeItem->append(['has_relationship']);

        return response()->json($typeItem);
    }

    public function update(Request $request, $id)
    {
        $typeItem = TypeItem::findOrFail($id);

        $typeItem->update($request->input());

        return response()->json($typeItem);
    }

    public function destroy($id)
    {
        $typeItem = TypeItem::findOrFail($id);
        $typeItem->delete();

        return response()->json(['success' => true]);
    }
}
