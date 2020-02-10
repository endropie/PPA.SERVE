<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\CategoryItem as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\CategoryItem;

class CategoryItems extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $itemCategories = CategoryItem::filter($filter)->get();
                break;

            case 'datagrid':
                $itemCategories = CategoryItem::filter($filter)->get();
                break;

            default:
                $itemCategories = CategoryItem::filter($filter)->collect();
                break;
        }

        return response()->json($itemCategories);
    }

    public function store(Request $request)
    {
        $itemCategory = CategoryItem::create($request->all());

        return response()->json($itemCategory);
    }

    public function show($id)
    {
        $itemCategory = CategoryItem::findOrFail($id);
        $itemCategory->append(['has_relationship']);

        return response()->json($itemCategory);
    }

    public function update(Request $request, $id)
    {
        $itemCategory = CategoryItem::findOrFail($id);

        $itemCategory->update($request->input());

        return response()->json($itemCategory);
    }

    public function destroy($id)
    {
        $brand = CategoryItem::findOrFail($id);
        $brand->delete();

        return response()->json(['success' => true]);
    }
}
