<?php
namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\Province as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Province;

class Provinces extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $provinces = Province::filterable()->get();    
                break;

            case 'datagrid':
                $provinces = Province::filterable()->get();
                
                break;

            default:
                $provinces = Province::collect();                
                break;
        }

        return response()->json($provinces);
    }

    public function store(Request $request)
    {
        $province = Province::create($request->all());

        return response()->json($province);
    }

    public function show($id)
    {
        $province = Province::findOrFail($id);
        $province->is_editable = (!$province->is_related);

        return response()->json($province);
    }

    public function update(Request $request, $id)
    {
        $province = Province::findOrFail($id);

        $province->update($request->input());

        return response()->json($province);
    }

    public function destroy($id)
    {
        $province = Province::findOrFail($id);
        $province->delete();

        return response()->json(['success' => true]);
    }
}
