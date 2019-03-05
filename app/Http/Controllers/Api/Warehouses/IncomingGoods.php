<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\IncomingGood as Request;
use App\Http\Controllers\ApiController;

use App\Models\Warehouse\IncomingGood; 
use App\Traits\GenerateNumber;

class IncomingGoods extends ApiController
{
    use GenerateNumber;

    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $incoming_goods = IncomingGood::filterable()->get();    
                break;

            case 'datagrid':    
                $incoming_goods = IncomingGood::with(['customer'])->filterable()->get();
                
                break;

            default:
                $incoming_goods = IncomingGood::collect();                
                break;
        }

        return response()->json($incoming_goods);
    }

    public function store(Request $request)
    {
        if(!$request->number) $request->merge(['number'=> $this->getNextIncomingGoodNumber()]);

        $incoming_good = IncomingGood::create($request->all());

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $incoming_good->incoming_good_items()->create($row);

            // Calculate stock on before the incoming Goods updated!
            $detail->item->increase($detail->unit_stock, 'incoming_good');
        }

        return response()->json($incoming_good);
    }

    public function show($id)
    {
        $incoming_good = IncomingGood::with(['incoming_good_items.item.item_units', 'incoming_good_items.unit'])->findOrFail($id);
        $incoming_good->is_editable = (!$incoming_good->is_related);
        
        return response()->json($incoming_good);
    }

    public function update(Request $request, $id)
    {
        $incoming_good = IncomingGood::findOrFail($id);
        $incoming_good->update($request->input());

        // Delete old incoming goods items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->incoming_good_items, 'id')));
        $delete_details = $incoming_good->incoming_good_items()->whereNotIn('id', $ids)->get();
        
        if($delete_details) {
          foreach ($delete_details as $detail) {
            // Calculate first, before deleting!
            $detail->item->decrease($detail->unit_stock, 'incoming_good');
            $detail->delete();
          }
        }

        // Update incoming goods items
        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $detail = $incoming_good->incoming_good_items()->find($row['id']);

            if($detail) {
                // Calculate stock on before the incoming Goods updated!
                $detail->item->decrease($detail->unit_stock, 'incoming_good');
                
                // update item row on the incoming Goods updated!
                $detail->update($row);
            }
            else{
                // create item row on the incoming Goods updated!
                $detail = $incoming_good->incoming_good_items()->create($row);
            }
            // Calculate stock on after the Incoming Goods updated!
            $detail->item->increase($detail->unit_stock, 'incoming_good');
        }

        return response()->json($incoming_good);
    }

    public function destroy($id)
    {
        $incoming_good = IncomingGood::findOrFail($id);
        $incoming_good->delete();

        return response()->json(['success' => true]);
    }
}
