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
                $packings = Packing::with([
                    'packing_items',
                    'packing_items.item'=> function($q) { $q->select(['id', 'code', 'part_number', 'part_name']); },
                    'customer'=> function($q) { $q->select(['id', 'code', 'name']); },
                    'shift'
                ])->filterable()->get();
                
                break;

            default:
                $packings = Packing::with([
                    'packing_items',
                    'packing_items.item'=> function($q) { $q->select(['id', 'code', 'part_number', 'part_name']); },
                    'customer'=> function($q) { $q->select(['id', 'code', 'name']); },
                    'shift'
                ])->collect();                
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

        $row = $request->packing_items;
        // Packing Items only 1 row detail (relation = $model->hasOne)
        if($row) {
            // Create the Packing item. Note: with "hasOne" Relation.
            $detail = $packing->packing_items()->create($request->packing_items);
            
            // Calculate stock on after the Packing items Created!
            $detail->item->increase($detail->unit_amount, 'FG', 'WO');

            $faults = $row['packing_item_faults'];
            for ($i=0; $i < count($faults); $i++) {
                $fault = $faults[$i];
                if($fault['fault_id'] || $fault['quantity'] ) {
                    // create fault on the Packing Goods Created!
                    $detail->packing_item_faults()->create($fault);
                }
            }

            // Calculate "NG" stock on after the Item Faults Created!
            $NG = (double) $detail->packing_item_faults()->sum('quantity');
            if ($NG > 0) {
                $detail->item->increase($NG, 'NG', 'WO');
            } 
            else if ($NG < 0) abort(500, 'Total NG FAILED');
        }

        $this->DATABASE::commit();
        return response()->json($packing);
    }

    public function show($id)
    {
        if(request('mode') == 'view') {
            $addWith = [
                'operator',
                'packing_items.work_order_item.work_order'
            ];
        }
        else $addWith = [];

        $packing = Packing::with(array_merge([
            'customer',
            'packing_items.item.item_units',
            'packing_items.unit',
            'packing_items.packing_item_faults.fault'
        ], $addWith))->findOrFail($id);

        // $packing->has_relationship = [];

        return response()->json($packing);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $packing = Packing::findOrFail($id);
        $packing->update($request->input());

        $row = $request->packing_items;
        // Packing Items only 1 row detail (relation = $model->hasOne)
        if($row) {
            $oldDetail = $packing->packing_items->find($row['id']);
            if($oldDetail) {
                // Calculate stock on before the Packing items updated!
                $oldDetail->item->decrease($oldDetail->unit_amount, 'FG', 'WO');
                // Calculate stock on before the NG items updated!
                $NG = (double) $packing->packing_items->packing_item_faults()->sum('quantity');
                $oldDetail->item->decrease($NG, 'NG', 'WO');
            }

            // Update or Create detail row
            $newDetail = $packing->packing_items->updateOrCreate(['id' => $row['id']], $row);
            // Calculate stock on after the Packing items updated!
            $newDetail->item->increase($newDetail->unit_amount, 'FG', 'WO');

            $faults = $row['packing_item_faults'];
            // Delete fault on the Packing Good updated!
            $packing->packing_items->packing_item_faults()
                ->whereNotIn('id', array_filter(array_column($faults, 'id')))
                ->delete();

            for ($i=0; $i < count($faults); $i++) {
                $fault = $faults[$i];
                if($fault['fault_id'] || $fault['quantity'] ) {
                    // create fault on the Packing Good updated!
                    $packing->packing_items->packing_item_faults()->updateOrCreate(['id'=>$fault['id']],$fault);
                }
            }
            
            // Calculate stock on after the NG items updated!
            $NG = (double) $packing->packing_items->packing_item_faults()->sum('quantity');
            if ($NG > 0) {
                $newDetail->item->increase($NG, 'NG', 'WO');
            }
            else if ($NG < 0) abort(500, 'Total NG FAILED');
        }

        $this->DATABASE::commit();
        return response()->json($packing);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();
        $packing = Packing::findOrFail($id);
        $detail = $packing->packing_items;

        // Calculate Stok Before deleting
        $detail->item->decrease($detail->unit_amount, 'FG', 'WO');
        $NG = (double) $detail->packing_item_faults()->sum('quantity');
        if ($NG > 0) {
            $detail->item->decrease($NG, 'NG', 'WO');
        }

        // Delete Packing.
        $detail->packing_item_faults()->delete();
        $detail->delete();
        $packing->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
