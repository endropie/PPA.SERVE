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
            $detail->item->increase($detail->unit_amount, $to);
        }

        $this->setRequestOrder($incoming_good, $incoming_good->order_mode);
        
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

        $incoming_good->setAppends(['has_relationship']);
        
        return response()->json($incoming_good);
    }

    public function update(Request $request, $id)
    {

        // DB::beginTransaction => Before the function process!
        $old = IncomingGood::with('incoming_good_items')->findOrFail($id)->toArray();
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);
        
        if ($incoming_good->is_relationship == true) return $this->error('SUBMIT FAIELD!', 'The data was relationship');

        $incoming_good->update($request->input());

        // Delete old incoming goods items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->incoming_good_items, 'id')));
        $delete_details = $incoming_good->incoming_good_items()->whereNotIn('id', $ids)->get();
        
        if($delete_details) {
          foreach ($delete_details as $detail) {
            // Calculate first, before deleting!
            $to = $incoming_good->transaction == 'RETURN' ? 'NGR' : 'FM';
            $detail->item->decrease($detail->unit_amount, $to);
            $detail->delete();
          }
        }

        // Update incoming goods items
        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $oldDetail = $incoming_good->incoming_good_items()->find($row['id']);
            if($oldDetail) {
                // Calculate stock on before the incoming Goods updated!
                $to = $incoming_good->transaction == 'RETURN' ? 'NGR' : 'FM';
                $oldDetail->item->decrease($oldDetail->unit_amount, $to);
            }

            // Update or Create detail row
            $newDetail = $incoming_good->incoming_good_items()->updateOrCreate(['id' => $row['id'] ?? null], $row);
            // Calculate stock on after the Incoming Goods updated!
            $to = $incoming_good->transaction == 'RETURN' ? 'NGR' : 'FM';
            $newDetail->item->increase($newDetail->unit_amount, $to);
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
        
        if ($incoming_good->is_relationship) return $this->error('SUBMIT FAIELD!', 'The data was relationship');

        if($details = $incoming_good->incoming_good_items) {
            foreach ($details as $detail) {
                $to = $incoming_good->transaction == 'RETURN' ? 'NGR' : 'FM';
                $detail->item->decrease($detail->unit_amount, $to);
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
        $idKeyDetails = [];
        if (!empty($old['incoming_good_items']) && count($old['incoming_good_items']) > 0) {
            foreach ($old['incoming_good_items'] as $value) {
                $idKeyDetails[] = $value['request_order_item_id'];
            }
        }
        
        if (strtoupper($mode) === 'NONE') {
            $model = RequestOrder::firstOrNew(['id' => $incoming_good->request_order_id]);
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
            $incoming_good->save(['request_order_id' => $model->id]);

            // Delete detail items, first!
            $model->request_order_items()->whereNotIn('id', array_values($idKeyDetails))->delete();
            // loop detail items on incoming good, for create.
            $rows = $incoming_good->incoming_good_items;
            foreach ($rows as $key => $row) {
                $detail = $model->request_order_items()->updateOrCreate(['id'=> $row['request_order_item_id']],
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
            $model = RequestOrder::where(function ($query) use ($incoming_good) {
                $query->whereDate('begin_date' , '<=', $incoming_good->date)
                      ->whereDate('until_date' , '>=', $incoming_good->date);
              })->where('customer_id', $incoming_good->customer_id)
                ->where('order_mode', $incoming_good->order_mode)
                ->latest()->first();

            // abort(501, json_encode($model));

            if(!$model) {
                $model = new RequestOrder;
                $begin = Carbon::parse($incoming_good->date)->startOfMonth()->format('Y-m-d');
                $until = Carbon::parse($incoming_good->date)->endOfMonth()->format('Y-m-d');

                $model->date  = $incoming_good->date;
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
                $incoming_good->save(['request_order_id' =>  $model->id]);
            }
            // Delete detail items, first!
            $newArrayIDs = $incoming_good->incoming_good_items()->whereNotNull('request_order_item_id')->get()->pluck('request_order_item_id');
            $var = ['old' => array_filter(array_values($idKeyDetails)), 'new' => $newArrayIDs];
            
            $test = $model->request_order_items()->where(
              function($q) use ($var) {
                $q->whereIn('id', $var['old']);
                $q->whereNotIn('id', $var['new']);
              }
            )->get();

            $model->request_order_items()->where(
              function($q) use ($var) {
                $q->whereIn('id', $var['old']);
                $q->whereNotIn('id', $var['new']);
              }
            )->delete();
            // loop detail items on incoming good, for create.
            $rows = $incoming_good->incoming_good_items;
            foreach ($rows as $key => $row) {
                $detail = $model->request_order_items()->updateOrCreate(['id'=> $row['request_order_item_id']],
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
        else if (strtoupper($mode) === 'PO') {
            // Not availabel execute..!
        }
    }
}
