<?php
namespace App\Http\Controllers\Api\Common;

use App\Filters\Filter as Filter ;
use App\Http\Requests\Request as Request;
use App\Http\Controllers\ApiController;
use App\Models\Common\CategoryItemPrice;

class CategoryItemPrices extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $category_item_prices = CategoryItemPrice::filter($filter)->get();
                break;

            case 'datagrid':
                $category_item_prices = CategoryItemPrice::with('customer')->filter($filter)->get();

                break;

            default:
                $category_item_prices = CategoryItemPrice::with('customer')->filter($filter)->collect();
                $category_item_prices->getCollection()->transform(function($rs) {
                    $rs->append(['has_relationship']);
                });
                break;
        }

        return response()->json($category_item_prices);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();


        $category_item_price = CategoryItemPrice::create($request->all());

        $category_item_price->setCommentLog("Category Price [$category_item_price->name] has been created!");

        $this->DATABASE::commit();

        return response()->json($category_item_price);
    }

    public function show($id)
    {
        $category_item_price = CategoryItemPrice::with('customer')->findOrFail($id);

        $category_item_price->append(['has_relationship']);

        return response()->json($category_item_price);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $category_item_price = CategoryItemPrice::findOrFail($id);

        $category_item_price->update($request->input());

        $category_item_price->setCommentLog("CategoryItemPrice [$category_item_price->name] has been updated!");

        $this->DATABASE::commit();

        return response()->json($category_item_price);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $category_item_price = CategoryItemPrice::findOrFail($id);

        if ($category_item_price->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be delete!");

        $category_item_price->delete();

        $category_item_price->setCommentLog("CategoryItemPrice [$category_item_price->name] has been deleted!");

        $this->DATABASE::commit();

        $this->error('LOLOS');

        return response()->json(['success' => true]);
    }
}
