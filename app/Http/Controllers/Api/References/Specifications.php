<?php

namespace App\Http\Controllers\Api\References;

// use Illuminate\Http\Request;
use App\Http\Requests\Reference\Specification as Request;
use App\Http\Controllers\ApiController;

use App\Models\Reference\Specification; 

class Specifications extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $specifications = Specification::filterable()->get();    
                break;

            case 'datagrid':
                $specifications = Specification::with(['color'])->filterable()->get();
                
                break;

            default:
                $specifications = Specification::collect();                
                break;
        }

        return response()->json($specifications);
    }

    public function store(Request $request)
    {
        $specification = Specification::create($request->all());

        // Delete pre production on the item updated!
        $specification->specification_details()->delete();

        $details = $request->specification_details;
        for ($i=0; $i < count($details); $i++) { 

            // create pre production on the item updated!
            $specification->specification_details()->create($details[$i]);
        }

        return response()->json($specification);
    }

    public function show($id)
    {
        $specification = Specification::with('specification_details')->findOrFail($id);
        $specification->is_editable = (!$specification->is_related);

        return response()->json($specification);
    }

    public function update(Request $request, $id)
    {
        $specification = Specification::findOrFail($id);

        $specification->update($request->input());

        // Delete pre production on the item updated!
        $specification->specification_details()->delete();

        $details = $request->specification_details;
        for ($i=0; $i < count($details); $i++) { 

            // create pre production on the item updated!
            $specification->specification_details()->create($details[$i]);
        }

        return response()->json($specification);
    }

    public function destroy($id)
    {
        $specification = Specification::findOrFail($id);
        $specification->delete();

        return response()->json(['success' => true]);
    }
}
