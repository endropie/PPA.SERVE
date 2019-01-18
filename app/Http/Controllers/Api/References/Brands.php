<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\Brand as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Brand;

class Brands extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $brands = Brand::filterable()->get();
                
                break;

            default:
                $brands = Brand::collect();                
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
        $brand->is_editable = (!$brand->is_related);

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
        //
    }
}
