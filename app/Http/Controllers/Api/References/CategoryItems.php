<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\CategoryItem as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\CategoryItem;

class CategoryItems extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $itemCategories = CategoryItem::filterable()->get();
                break;

            case 'datagrid':
                $itemCategories = CategoryItem::filterable()->get();
                break;

            default:
                $itemCategories = CategoryItem::collect();                
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
        $itemCategory->is_editable = (!$itemCategory->is_related);

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
        //
    }
}
