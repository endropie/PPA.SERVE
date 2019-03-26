<?php

namespace App\Http\Controllers\Api\Factories;

use App\Http\Requests\Factory\Packing as Request;
use App\Http\Controllers\ApiController;

use App\Models\Factory\Packing; 
use App\Traits\GenerateNumber;

class Packings extends ApiController
{
    use GenerateNumber;

    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $packings = Packing::filterable()->get();    
                break;

            case 'datagrid':    
                $packings = Packing::with(['customer','work_order', 'shift', 'packing_items.item'])->filterable()->get();
                
                break;

            default:
                $packings = Packing::with(['customer','work_order', 'shift', 'packing_items.item'])->collect();                
                break;
        }

        return response()->json($packings);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextPackingNumber()]);

        // Create the Packing Goods.
        $packing = Packing::create($request->all());

        // Create the Packing item. Note: with "hasOne" Relation.
        $packing_items = $packing->packing_items()->create($request->packing_items);
        
        // Calculate stock on after the Packing items updated!
        $packing_items->item->increase($packing_items->unit_stock, 'FG', 'WO');

        $detail = $request->packing_items;
        $fault_rows = $detail['packing_item_faults'];
        for ($i=0; $i < count($fault_rows); $i++) {

            $row = $fault_rows[$i];
            if($row['fault_id'] || $row['quantity'] ) {
                // create fault on the Packing Goods updated!
                $packing_items->packing_item_faults()->create($row);
            }
        }

        $this->DATABASE::commit();
        return response()->json($packing);
    }

    public function show($id)
    {
        $packing = Packing::with(['packing_items.item.item_units','packing_items.packing_item_faults.fault'])->findOrFail($id);
        $packing->is_editable = (!$packing->is_related);

        return response()->json($packing);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $packing = Packing::findOrFail($id);
        $packing_items =  $packing->packing_items;
        // Back Calculate stock on before the Packing items updated!
        $packing_items->item->decrease($packing_items->unit_stock, 'FG', 'WO');

        // Update the Packing items.
        $packing->update($request->input());
        $packing_items->update($request->packing_items);

        // Calculate stock on after the Packing items updated!
        $packing_items->item->increase($packing_items->unit_stock, 'FG', 'WO');

        // Delete fault on the Packing Good updated!
        $packing_items->packing_item_faults()->delete();

        $detail = $request->packing_items;
        $fault_rows = $detail['packing_item_faults'];
        for ($i=0; $i < count($fault_rows); $i++) {

            $row = $fault_rows[$i];
            if($row['fault_id'] || $row['quantity'] ) {
                // create fault on the Packing Good updated!
                $packing_items->packing_item_faults()->create($row);
            }
        }

        $this->DATABASE::commit();
        return response()->json($packing);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();
        $packing = Packing::findOrFail($id);
        $packing->packing_items()->faults()->delete();
        $packing->packing_items()->delete();
        $packing->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
