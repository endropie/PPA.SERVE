<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\IncomingGood as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Warehouse\IncomingGood as Filters;
use App\Models\Warehouse\IncomingGood;
use App\Models\Income\RequestOrder;
use App\Models\Income\PreDelivery;
use App\Traits\GenerateNumber;

class IncomingGoods extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $incoming_goods = IncomingGood::filter($filters)->get();
                break;

            case 'datagrid':
                $incoming_goods = IncomingGood::with(['customer'])->filter($filters)->latest()->get();
                $incoming_goods->each->setAppends(['is_relationship']);
                break;

            default:
                $incoming_goods = IncomingGood::with(['customer'])->filter($filters)->latest()->collect();
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
        // if($request->transaction == 'RETURN') $request->merge(['order_mode'=> 'NONE']);

        $incoming_good = IncomingGood::create($request->all());

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $incoming_good->incoming_good_items()->create($row);
            if (!$detail->item->enable) $this->error("PART [". $detail->item->code . "] DISABLED");

        }

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function show($id)
    {
        $incoming_good = IncomingGood::withTrashed()->with([
            'customer',
            'request_order',
            'incoming_good_items.item.item_units',
            'incoming_good_items.unit'
        ])->findOrFail($id);

        $incoming_good->setAppends(['is_relationship','has_relationship']);

        return response()->json($incoming_good);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') === 'rejection') return $this->rejection($request, $id);
        if(request('mode') === 'restoration') return $this->restoration($request, $id);
        if(request('mode') === 'validation') return $this->validation($request, $id);
        if(request('mode') === 'revision') return $this->revision($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        if ($incoming_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');
        if ($incoming_good->is_relationship) $this->error('The data has relationships, is not allowed to be changed');

        $incoming_good->update($request->input());

        // Before Update Force delete incoming goods items
        $incoming_good->incoming_good_items()->forceDelete();

        // Update incoming goods items
        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Update or Create detail row
            $detail = $incoming_good->incoming_good_items()->create($row);
            if (!$detail->item->enable) $this->error("PART [". $detail->item->code . "] DISABLED");
        }

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($incoming_good->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        if($mode == "DELETED" && $incoming_good->status != 'OPEN') $this->error("The data $incoming_good->status state, is not allowed to be $mode");

        if($mode == 'VOID') {
            $incoming_good->status = "VOID";
            $incoming_good->save();
        }

        if($details = $incoming_good->incoming_good_items) {
            foreach ($details as $detail) {
                $detail->item->distransfer($detail);
                $detail->delete();
            }
        }

        $incoming_good->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function rejection($request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        if ($incoming_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        $incoming_good->status = 'REJECTED';
        $incoming_good->save();

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function restoration($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = IncomingGood::findOrFail($id);
        $details = $revise->incoming_good_items;
        foreach ($details as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        if($request->number) {
            $max = (int) IncomingGood::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number' => ($max + 1)]);
        }

        if(!$request->transaction == 'RETURN') $request->merge(['order_mode'=> 'NONE']);

        $incoming_good = IncomingGood::create($request->all());

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $detail = $incoming_good->incoming_good_items()->create($row);
        }

        $revise->revise_id = $incoming_good->id;
        $revise->save();
        $revise->delete();

        // $this->error('LOLOS');

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function validation($request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        $rows = $request->incoming_good_items ?? [];

        foreach ($rows as $row) {
            $detail = $incoming_good->incoming_good_items()->find($row["id"]);
            $detail->update($row);
        }

        if ($incoming_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        foreach ($incoming_good->incoming_good_items as $detail) {
            // Calculate stock on "validation" Incoming Goods!
            $to = $incoming_good->transaction == 'RETURN' ? 'RET' : 'FM';
            $detail->item->transfer($detail, $detail->unit_valid, $to);

            if (strtoupper($incoming_good->order_mode) === 'ACCUMULATE') {
                $detail->item->transfer($detail, $detail->unit_valid, 'RDO.REG');
            }
        }

        if (strtoupper($incoming_good->order_mode) === 'NONE') {
            $this->storeRequestOrder($incoming_good);
        }

        $incoming_good->status = 'VALIDATED';
        $incoming_good->save();

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function revision($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = IncomingGood::findOrFail($id);
        $details = $revise->incoming_good_items;
        foreach ($details as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        if($request->number) {
            $max = (int) IncomingGood::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number' => ($max + 1)]);
        }

        if(!$request->transaction == 'RETURN') $request->merge(['order_mode'=> 'NONE']);

        $incoming_good = IncomingGood::create($request->all());

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $row['valid'] = $row['quantity'];
            $detail = $incoming_good->incoming_good_items()->create($row);

            if (isset($row['request_order_item_id'])) {
                $request_order_item = $incoming_good->request_order->request_order_items()->find($row['request_order_item_id']);
                $detail->request_order_item()->associate($request_order_item);
                $detail->save();
            }

            $to = $incoming_good->transaction == 'RETURN' ? 'RET' : 'FM';
            $detail->item->transfer($detail, $detail->unit_valid, $to);

            if (strtoupper($incoming_good->order_mode) === 'ACCUMULATE') {
                $detail->item->transfer($detail, $detail->unit_valid, 'RDO.REG');
            }
        }

        if (strtoupper($incoming_good->order_mode) === 'NONE') {
            $this->reviseRequestOrder($revise, $incoming_good);
        }

        $incoming_good->status = 'VALIDATED';
        $incoming_good->save();

        $revise->status = 'REVISED';
        $revise->revise_id = $incoming_good->id;
        $revise->save();
        $revise->delete();

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    private function reviseRequestOrder($revise, $incoming_good) {

        $exclude_details = collect(request('incoming_good_items'))->map(function ($item) { return $item['id']; });

        $request_order = $incoming_good->request_order;

        $details = $revise->incoming_good_items ?? [];
        foreach ($details as $detail) {
            $request_order_item = $detail->request_order_item;
            $delivery_order_items = (bool) $request_order_item
                ? $detail->request_order_item->delivery_order_items
                : [];

            // Rollback All stock!
            $detail->request_order_item->item->distransfer($detail->request_order_item);
            // Delete item has Removed!
            if (!in_array($detail->id, $exclude_details->toArray())) {
                $delivery_order_items->map(function ($detail) {
                    if (strtoupper($detail->delivery_order->status) == 'CLOSED') {
                        $this->error("DATA has Relation SJDO#". $detail->delivery_order->number ."[CLOSED]. REVISION has not allowed!");
                    }
                    // Unset Relation detail for detail item removed
                    $detail->request_order_item()->associate(null);
                    $detail->save();
                });

                $detail->request_order_item()->forceDelete();
            }

            // Unset Relation detail for revision
            $detail->request_order_item()->associate(null);
            $detail->save();
        }

        $details = $incoming_good->incoming_good_items;
        foreach ($details as $detail) {
            $request_order_item = $detail->request_order_item;

            $field = collect($detail)->only(['item_id', 'unit_id', 'unit_rate'])->merge(['quantity'=> $detail->valid, 'price'=>0]);

            $request_order_item = $request_order
                ->request_order_items()
                ->updateOrCreate(['id'=>$detail->request_order_item_id],$field->toArray());
            $detail->request_order_item()->associate($request_order_item);
            $detail->save();

            // if ($request_order_item->unit_amount < $request_order_item->total_delivery_order_item) {
            //     $this->error("PART[". $request_order_item->item->part_name ."] total unit invalid. REVISION has not allowed!");
            // }

            $TO = $request_order->transaction == 'RETURN' ? 'RDO.RET' : 'RDO.REG';
            $detail->request_order_item->item->transfer($detail->request_order_item, $detail->unit_amount, $TO);
        }


        $revise->request_order()->associate(null);
        $revise->save();
    }

    private function storeRequestOrder($incoming_good) {
        $incoming_good = $incoming_good->fresh();

        $mode = $incoming_good->order_mode;

        if (strtoupper($mode) === 'NONE') {
            $number = $this->getNextRequestOrderNumber($incoming_good->date);

            $model = RequestOrder::create([
                'number'        => $number,
                'date'          => $incoming_good->date,
                'customer_id'   => $incoming_good->customer_id,
                'reference_number' => $incoming_good->reference_number,
                'transaction'    => $incoming_good->transaction,
                'order_mode'    => $incoming_good->order_mode,
                'description'   => "NONE P/O. AUTO CREATE PO BASED ON INCOMING: $incoming_good->number",
            ]);
            $incoming_good->request_order()->associate($model);
            $incoming_good->save();

            $rows = $incoming_good->incoming_good_items;
            foreach ($rows as $row) {
                $fields = collect($row)->only(['item_id', 'unit_id', 'unit_rate'])->merge(['quantity'=>$row['valid'], 'price'=>0])->toArray();
                $detail = $model->request_order_items()->create($fields);

                $TO = $incoming_good->transaction == 'RETURN' ? 'RDO.RET' : 'RDO.REG';
                $detail->item->transfer($detail, $detail->unit_amount, $TO);

                $row->request_order_item()->associate($detail);
                $row->save();

            }
        }
    }

    // NOT CREATE PREDELIVERY 07/08
    private function storePreDelivery($incoming_good) {

        $incoming_good = $incoming_good->fresh();

        if ($incoming_good->order_mode === 'NONE') {

            $number = $this->getNextPreDeliveryNumber($incoming_good->date);

            $model = PreDelivery::create([
                'number'        => $number,
                'date'          => $incoming_good->date,
                'customer_id'   => $incoming_good->customer_id,
                'customer_name'   => $incoming_good->customer->name,
                'customer_phone'   => $incoming_good->customer->phone,
                'customer_address'   => $incoming_good->customer->address,

                'transaction'   => $incoming_good->transaction,
                'order_mode'    => $incoming_good->order_mode,
                'plan_begin_date'  => $incoming_good->date,
                'plan_until_date'  => $incoming_good->date,
                'reference_number' => $incoming_good->reference_number,
                'description'   => "NONE P/O. AUTO CREATE PO BASED ON INCOMING: $incoming_good->number",
            ]);


            $incoming_good->pre_delivery_id = $model->id;
            $incoming_good->save();

            $rows = $incoming_good->incoming_good_items;
            foreach ($rows as $row) {
                $fields = collect($row)->only(['item_id', 'unit_id', 'unit_rate', 'quantity'])->toArray();
                $detail = $model->pre_delivery_items()->create($fields);

                // COMPUTE ITEMSTOCK !!
                $STOCKIST = $incoming_good->transaction == 'RETURN' ? 'PDO.RET' : 'PDO.REG';

                $detail->item->transfer($detail, $detail->unit_amount, $STOCKIST);
            }
        }
    }
}
