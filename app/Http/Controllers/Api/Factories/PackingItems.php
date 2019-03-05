<?php

namespace App\Http\Controllers\Api\Factories;

use App\Http\Requests\Factory\PackingItem as Request;
use App\Http\Controllers\ApiController;

use App\Models\Factory\PackingItem; 
use App\Traits\GenerateNumber;

class PackingItems extends ApiController
{
    use GenerateNumber;

    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $packing_items = PackingItem::filterable()->get();    
                break;

            case 'datagrid':    
                $packing_items = PackingItem::with(['customer', 'item', 'work_order', 'shift', 'type_fault', 'type_worktime'])->filterable()->get();
                
                break;

            default:
                $packing_items = PackingItem::collect();                
                break;
        }

        return response()->json($packing_items);
    }

    public function store(Request $request)
    {
        
        if(!$request->number) $request->merge(['number'=> $this->getNextPackingItemNumber()]);

        // Create the Packing items.
        $packing_item = PackingItem::create($request->all());
        
        // Calculate stock on after the Packing items updated!
        $packing_item->item->increase($packing_item->unit_stock, 'packing_item', 'work_order');

        $fault_rows = $request->packing_item_faults;
        for ($i=0; $i < count($fault_rows); $i++) {

            $row = $fault_rows[$i];
            if($row['fault_id'] || $row['quantity'] ) {
                // create fault on the Packing Goods updated!
                $packing_item->packing_item_faults()->create($row);
            }
        }

        return response()->json($packing_item);
    }

    public function show($id)
    {
        $packing_item = PackingItem::with(['item.item_units','packing_item_faults.fault'])->findOrFail($id);
        $packing_item->is_editable = (!$packing_item->is_related);

        return response()->json($packing_item);
    }

    public function update(Request $request, $id)
    {
        $packing_item = PackingItem::findOrFail($id);

        // Back Calculate stock on before the Packing items updated!
        $packing_item->item->decrease($packing_item->unit_stock, 'packing_item', 'work_order');

        // Update the Packing items.
        $packing_item->update($request->input());

        // Calculate stock on after the Packing items updated!
        $packing_item->item->increase($packing_item->unit_stock, 'packing_item', 'work_order');

        // Delete fault on the Packing Good updated!
        $packing_item->packing_item_faults()->delete();

        $fault_rows = $request->packing_item_faults;
        for ($i=0; $i < count($fault_rows); $i++) {

            $row = $fault_rows[$i];
            if($row['fault_id'] || $row['quantity'] ) {
                // create fault on the Packing Good updated!
                $packing_item->packing_item_faults()->create($row);
            }
        }

        return response()->json($packing_item);
    }

    public function destroy($id)
    {
        $packing_item = PackingItem::findOrFail($id);
        $packing_item->packing_item_faults()->delete();
        $packing_item->delete();

        return response()->json(['success' => true]);
    }
}
