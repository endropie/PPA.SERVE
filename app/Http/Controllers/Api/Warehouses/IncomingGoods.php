<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\IncomingGood as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Warehouse\IncomingGood as Filters;
use App\Models\Warehouse\IncomingGood; 
use App\Traits\GenerateNumber;
use Carbon\Carbon;

class IncomingGoods extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':            
                $incoming_goods = IncomingGood::filterable()->get();    
                break;

            case 'datagrid':    
                $incoming_goods = IncomingGood::with(['customer'])->filterable()->get();
                
                break;

            default:
                $incoming_goods = IncomingGood::with(['customer'])->filter($filters)->collect();                
                break;
        }

        return response()->json($incoming_goods);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextIncomingGoodNumber()]);

        $incoming_good = IncomingGood::create($request->all());

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $incoming_good->incoming_good_items()->create($row);

            // Calculate stock on before the incoming Goods updated!
            $detail->item->increase($detail->unit_stock, 'FM');
        }

        $this->setRequestOrder($incoming_good, $incoming_good->order_mode);
        
        // DB::Commit => Before return function!
        $this->DATABASE::commit();
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
        // DB::beginTransaction => Before the function process!
        $old = IncomingGood::with('incoming_good_items')->findOrFail($id)->toArray();
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);
        $incoming_good->update($request->input());

        // Delete old incoming goods items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->incoming_good_items, 'id')));
        $delete_details = $incoming_good->incoming_good_items()->whereNotIn('id', $ids)->get();
        
        if($delete_details) {
          foreach ($delete_details as $detail) {
            // Calculate first, before deleting!
            $detail->item->decrease($detail->unit_stock, 'FM');
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
                $detail->item->decrease($detail->unit_stock, 'FM');
                
                // update item row on the incoming Goods updated!
                $detail->update($row);
            }
            else{
                // create item row on the incoming Goods updated!
                $detail = $incoming_good->incoming_good_items()->create($row);
            }
            // Calculate stock on after the Incoming Goods updated!
            $detail->item->increase($detail->unit_stock, 'FM');
        }

        // Create or update Request Order as referense
        $this->setRequestOrder($incoming_good, $incoming_good->order_mode, $old);

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);
        if($details = $incoming_good->incoming_good_items) {
            foreach ($details as $detail) {
                $detail->item->decrease($detail->unit_stock, 'FM');
            }
        }
        $incoming_good->incoming_good_items()->delete();
        $incoming_good->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    private function setRequestOrder($incoming_good, $mode = 'NONE', $old = []) 
    {
        $oldArrayIDs = [];
        if (!empty($old['incoming_good_items']) && count($old['incoming_good_items']) > 0) {
            foreach ($old['incoming_good_items'] as $value) {
                $oldArrayIDs[$value['id']] = $value['request_order_item_id'];
            }
        }
        
        if (strtoupper($mode) === 'NONE') {
            $model = \App\Models\Income\RequestOrder::firstOrNew(['id' => $incoming_good->request_order_id]);
            $model->begin_date  = $incoming_good->date;
            $model->until_date  = $incoming_good->date;
            $model->customer_id = $incoming_good->customer_id;
            $model->reference_number = $incoming_good->reference_number;
            $model->reference_date   = $incoming_good->reference_date;

            $model->order_mode   = $incoming_good->order_mode;
            $model->description   = "NONE P/O. AUTO CREATE PO BASED ON INCOMING: $incoming_good->number";
            // For model update 
            if(!$model->id) {
                $model->number = $this->getNextRequestOrderNumber($incoming_good->date);
            }
            $model->save();
            $incoming_good->update(['request_order_id' => $model->id]);

            // Delete detail items, first!
            $model->request_order_items()->whereNotIn('id', array_values($oldArrayIDs))->delete();
            // loop detail items on incoming good, for create.
            $rows = $incoming_good->incoming_good_items;
            foreach ($rows as $key => $row) {
                $detail = $model->request_order_items()->firstOrCreate(['id'=> $row['request_order_item_id']],
                [
                    'item_id'   => $row['item_id'],
                    'unit_id'   => $row['unit_id'],
                    'unit_rate' => $row['unit_rate'],
                    'quantity'  => $row['quantity'],
                    'price' => 0,
                ]);
                
                $incoming_good_item = $incoming_good->incoming_good_items()->find($row['id']);
                $incoming_good_item->request_order_item_id = $detail->id;
                $incoming_good_item->save();

            }
        }
        else if (strtoupper($mode) === 'ACCUMULATE') {
            $model = \App\Models\Income\RequestOrder::where(function ($query) use ($incoming_good) {
                $query->whereDate('begin_date' , '<=', $incoming_good->date)
                      ->whereDate('until_date' , '>=', $incoming_good->date);
              })->where('customer_id', $incoming_good->customer_id)
                ->where('order_mode', $incoming_good->order_mode)
                ->latest()->first();

            // abort(501, json_encode($model));

            if(!$model) {
                $model = new \App\Models\Income\RequestOrder;
                $begin = Carbon::parse($incoming_good->date)->startOfMonth()->format('Y-m-d');
                $until = Carbon::parse($incoming_good->date)->endOfMonth()->format('Y-m-d');

                $model->begin_date  = $begin;
                $model->until_date  = $until;
                $model->customer_id = $incoming_good->customer_id;
                $model->order_mode   = $incoming_good->order_mode;
                $model->description   = "ACCUMULATE P/O. FOR ". $begin." - ". $until;
                // For model update 
                if(!$model->id) {
                    $model->number = $this->getNextRequestOrderNumber($incoming_good->date);
                }
                $model->save();
                $incoming_good->update(['request_order_id' => $model->id]);            
            }
            // Delete detail items, first!
            $newArrayIDs = $model->request_order_items()->get()->pluck('id');
            $var = ['old' => array_values($oldArrayIDs), 'new' => $newArrayIDs];
            
            $model->request_order_items()->where(
              function($q) use ($var) {
                $q->whereIn('id', $var['old'])
                  ->whereNotIn('id', $var['new']);
              }
            )->delete();
            // loop detail items on incoming good, for create.
            $rows = $incoming_good->incoming_good_items;
            foreach ($rows as $key => $row) {
                $detail = $model->request_order_items()->firstOrCreate(['id'=> $row['request_order_item_id']],
                [
                    'item_id'   => $row['item_id'],
                    'unit_id'   => $row['unit_id'],
                    'unit_rate' => $row['unit_rate'],
                    'quantity'  => $row['quantity'],
                    'price' => 0,
                ]);

                $incoming_good_item = $incoming_good->incoming_good_items()->find($row['id']);
                $incoming_good_item->request_order_item_id = $detail->id;
                $incoming_good_item->save();
            }

            
        }
        else if (strtoupper($mode) === 'ACCUMULATE') {
            // Code...
        }
    }
}
