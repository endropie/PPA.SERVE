<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Requests\Common\Item as Request;
use App\Http\Controllers\ApiController;

use App\Models\Common\Item; 

class Items extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
          case 'all':            
            $items = Item::with(['item_prelines','item_units','unit'])->filterable()->get();    
          break;

          case 'datagrid':
            $items = Item::with(['item_prelines','item_units', 'brand', 'customer', 'specification'])->orderBy('id','DESC')->filterable()->get();
            
          break;

          case 'itemstock':
            $items = Item::filterable()->get(['id'])->map->append('totals');
            
          break;

          default:
            $items = Item::with(['item_prelines','item_units', 'brand', 'customer', 'specification'])->collect();                
          break;
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $item = Item::create($request->all());

        $preline_rows = $request->item_prelines;
        for ($i=0; $i < count($preline_rows); $i++) {
            // create pre production on the item updated!
            $item->item_prelines()->create($preline_rows[$i]);
        }

        $unit_rows = $request->item_units;
        for ($i=0; $i < count($unit_rows); $i++) { 
            // create item units on the item updated!
            $item->item_units()->create($unit_rows[$i]);
        }

        return response()->json($item);
    }

    public function show($id)
    {
        $item = Item::with(['item_prelines', 'item_units'])->findOrFail($id);
        $item->is_editable = (!$item->is_related);

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $item->update($request->input());

        // Delete pre production on the item updated!
        $item->item_prelines()->delete();
        $preline_rows = $request->item_prelines;
        for ($i=0; $i < count($preline_rows); $i++) { 
            // create pre production on the item updated!
            $item->item_prelines()->create($preline_rows[$i]);
        }

        // Delete item units on the item updated!
        $item->item_units()->delete();
        $unit_rows = $request->item_units;
        for ($i=0; $i < count($unit_rows); $i++) { 
            // create item units on the item updated!
            $item->item_units()->create($unit_rows[$i]);
        }

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json(['success' => true]);
    }
}
