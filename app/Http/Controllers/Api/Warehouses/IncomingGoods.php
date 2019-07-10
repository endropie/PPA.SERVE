<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\IncomingGood as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Warehouse\IncomingGood as Filters;
use App\Models\Warehouse\IncomingGood;
use App\Models\Income\RequestOrder;
use App\Traits\GenerateNumber;
use Carbon\Carbon;

class IncomingGoods extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':            
                $incoming_goods = IncomingGood::filterable()->filter($filters)->get();    
                break;

            case 'datagrid':    
                $incoming_goods = IncomingGood::with(['customer'])->filterable()->get();
                $incoming_goods->each->setAppends(['is_relationship']);
                break;

            default:
                $incoming_goods = IncomingGood::with(['customer'])->filter($filters)->collect();
                $incoming_goods->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($incoming_goods);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextIncomingGoodNumber()]);
        if(!$request->transaction == 'RETURN') $request->merge(['order_mode'=> 'NONE']);

        $incoming_good = IncomingGood::create($request->all());

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $incoming_good->incoming_good_items()->create($row);

            // Calculate stock on before the incoming Goods updated!
            $to = $incoming_good->transaction == 'RETURN' ? 'NGR' : 'FM';
            $detail->item->transfer($detail, $detail->unit_amount, $to);
        }

        $this->storeRequestOrder($incoming_good);
        
        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function show($id)
    {
        $incoming_good = IncomingGood::with([
            'customer',
            'incoming_good_items.item.item_units',
            'incoming_good_items.unit'
        ])->findOrFail($id);

        $incoming_good->setAppends(['is_relationship','has_relationship']);
        
        return response()->json($incoming_good);
    }

    public function update(Request $request, $id)
    {

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);
        
        if ($incoming_good->is_relationship == true) $this->error('The data has relationships, is not allowed to be changed');

        $incoming_good->update($request->input());

        // Delete old incoming goods items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->incoming_good_items, 'id')));
        $deletes = $incoming_good->incoming_good_items;
        if($deletes) {
          foreach ($deletes as $detail) {
            // Calculate first, before deleting!
            $detail->item->distransfer($detail);
            $detail->delete();
          }
        }

        // Update incoming goods items
        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Update or Create detail row
            $detail = $incoming_good->incoming_good_items()->create($row);
            // Calculate stock on after the Incoming Goods updated!
            $to = $incoming_good->transaction == 'RETURN' ? 'NGR' : 'FM';
            $detail->item->transfer($detail, $detail->unit_amount, $to);
        }

        // Create or update Request Order as referense
        $this->storeRequestOrder($incoming_good);

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function destroy($id)
    {
        if(strtoupper(request('mode')) == 'VOID') {
            return $this->void($id);
        }

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);
        
        if ($incoming_good->is_relationship) $this->error('The data has relationships, is not allowed to be deleted');
        if ($incoming_good->status != 'OPEN') $this->error('The data has not OPEN, is not allowed to be deleted');
        
        if($request_order = $incoming_good->request_order) {
            if($incoming_good->order_mode == 'NONE') {
                foreach ($request_order->request_order_items as $detail) {
                    $detail->item->distransfer($detail);
                    if($detail->item->stock('RO')->total < (0)) $this->error('Data is not allowed to be deleted!');
                    $detail->delete();
                }
                $request_order->delete();
            }
            else if($incoming_good->order_mode == 'ACCUMULATE') {
                if($details = $incoming_good->incoming_good_items) {
                    foreach ($details as $detail) {
                        $detail->request_order_item->item->distransfer($detail);
                        if($detail->item->stock('RO')->total < (0)) $this->error('Data is not allowed to be deleted!');
                        $detail->request_order_item->delete();
                    }
                }
            }    
        }

        if($details = $incoming_good->incoming_good_items) {
            foreach ($details as $detail) {
                $to = $incoming_good->transaction == 'RETURN' ? 'NGR' : 'FM';
                $detail->item->distransfer($detail);
            }
        }

        $incoming_good->incoming_good_items()->delete();
        $incoming_good->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function void($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);
        
        if ($incoming_good->status == 'VOID') $this->error('The data has VOID state, is not allowed to be void!');

        
        if($request_order = $incoming_good->request_order) {
            if($incoming_good->order_mode == 'NONE') {
                foreach ($request_order->request_order_items as $detail) {
                    $detail->item->distransfer($detail);
                    // if($detail->item->stock('RO')->total < (0)) $this->error('Data is not allowed to be deleted!');
                    // $detail->delete();
                }
                // $request_order->delete();
            }
            else if($incoming_good->order_mode == 'ACCUMULATE') {
                if($details = $incoming_good->incoming_good_items) {
                    foreach ($details as $detail) {
                        $detail->request_order_item->item->distransfer($detail);
                        // if($detail->item->stock('RO')->total < (0)) $this->error('Data is not allowed to be deleted!');
                        // $detail->request_order_item->delete();
                    }
                }
            }    
        }

        if($details = $incoming_good->incoming_good_items) {
            foreach ($details as $detail) {
                $to = $incoming_good->transaction == 'RETURN' ? 'NGR' : 'FM';
                $detail->item->distransfer($detail);
            }
        }

        // $incoming_good->incoming_good_items()->delete();
        // $incoming_good->delete();
        $incoming_good->status = 'VOID';
        $incoming_good->save();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    private function storeRequestOrder($incoming_good) {
        $incoming_good = $incoming_good->fresh();
        
        $mode = $incoming_good->order_mode;

        if (strtoupper($mode) === 'NONE') {
            $model = RequestOrder::firstOrNew(['id' => $incoming_good->request_order_id]);
            
            // if ($model->is_relationship == true) $this->error('The data has relationships, is not allowed to be changed');

            $model->date  = $incoming_good->date;
            $model->begin_date  = null;
            $model->until_date  = null;
            $model->customer_id = $incoming_good->customer_id;
            $model->reference_number = $incoming_good->reference_number;

            $model->order_mode   = $incoming_good->order_mode;
            $model->description   = "NONE P/O. AUTO CREATE PO BASED ON INCOMING: $incoming_good->number";
            // For model update 
            if(!$model->id) {
                $model->number = $this->getNextRequestOrderNumber($incoming_good->date);
            }
            $model->save();
            $incoming_good->request_order_id = $model->id;
            $incoming_good->save();

            foreach ($model->request_order_items as $detail) {
                // COMPUTE ITEMSTOCK !!
                $detail->item->distransfer($detail);
                if($detail->item->stock('RO')->total < (0)) $this->error('Data is not allowed to be changed!');
                $detail->delete();  
            }

            $rows = $incoming_good->incoming_good_items;
            foreach ($rows as $key => $row) {
                $fields = collect($row)->only(['item_id', 'unit_id', 'unit_rate', 'quantity'])->merge(['price'=>0])->toArray();
                
                $detail = $model->request_order_items()->create($fields);

                // COMPUTE ITEMSTOCK !!
                $detail->item->transfer($detail, $detail->unit_amount, 'RO');
                
                $row->request_order_item_id = $detail->id;
                $row->save();

            }
        }
        else if (strtoupper($mode) === 'ACCUMULATE') {
            // loop detail items on incoming good, for create.
            $incoming_good->incoming_good_items->each( function($detail) {
                $detail->item->transfer($detail, $detail->unit_amount, 'RO');
            });
        }

    }
}
