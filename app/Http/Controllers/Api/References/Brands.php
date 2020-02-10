<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Brand as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Brand;

class Brands extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $brands = Brand::filter($filter)->get();
                break;

            case 'datagrid':
                $brands = Brand::filter($filter)->get();

                break;

            default:
                $brands = Brand::filter($filter)->collect();
                break;
        }

        return response()->json($brands);
    }

    public function store(Request $request)
    {
        $brand = Brand::create($request->all());

        return response()->json($brand);
    }

    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->append(['has_relationship']);

        return response()->json($brand);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $brand->update($request->input());

        return response()->json($brand);
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json(['success' => true]);
    }
}
