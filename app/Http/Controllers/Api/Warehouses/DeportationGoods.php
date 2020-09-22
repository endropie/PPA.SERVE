<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\DeportationGood as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Warehouse\DeportationGood as Filter;
use App\Models\Warehouse\DeportationGood;
use App\Traits\GenerateNumber;

class DeportationGoods extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $deportation_goods = DeportationGood::filter($filter)->get();
                break;

            case 'datagrid':
                $deportation_goods = DeportationGood::with(['customer'])->filter($filter)->latest()->get();
                $deportation_goods->each->append(['is_relationship']);
                break;

            default:
                $deportation_goods = DeportationGood::with(['created_user','customer'])->filter($filter)->latest()->collect();
                $deportation_goods->getCollection()->transform(function($item) {
                    $item->append(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($deportation_goods);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        if (!$request->number) $request->merge([
            'number'=> $this->getNextDeportationGoodNumber($request->input('date'))
        ]);

        $deportation_good = DeportationGood::create($request->all());

        $rows = $request->deportation_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $deportation_good->deportation_good_items()->create($row);
            if (!$detail->item->enable) $this->error("PART [". $detail->item->code . "] DISABLED");

        }

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($deportation_good);
    }

    public function show($id)
    {
        $deportation_good = DeportationGood::withTrashed()->with([
            'customer',
            'deportation_good_items.item.item_units',
            'deportation_good_items.unit',
            'created_user'
        ])->findOrFail($id);

        $deportation_good->append(['is_relationship','has_relationship']);

        return response()->json($deportation_good);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') === 'rejection') return $this->rejection($request, $id);
        if(request('mode') === 'validation') return $this->validation($request, $id);
        if(request('mode') === 'revision') return $this->revision($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $deportation_good = DeportationGood::findOrFail($id);

        if ($deportation_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');
        if ($deportation_good->is_relationship) $this->error('The data has relationships, is not allowed to be changed');

        $deportation_good->update($request->input());

        // Before Update Force delete incoming goods items
        $deportation_good->deportation_good_items()->forceDelete();

        // Update incoming goods items
        $rows = $request->deportation_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Update or Create detail row
            $detail = $deportation_good->deportation_good_items()->create($row);
            if (!$detail->item->enable) $this->error("PART [". $detail->item->code . "] DISABLED");
        }

        $this->DATABASE::commit();
        return response()->json($deportation_good);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $deportation_good = DeportationGood::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($deportation_good->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        if($mode == "DELETED" && $deportation_good->status != 'OPEN') $this->error("The data $deportation_good->status state, is not allowed to be $mode");

        if($mode == 'VOID') {
            $deportation_good->status = "VOID";
            $deportation_good->save();
        }

        if($details = $deportation_good->deportation_good_items) {
            foreach ($details as $detail) {
                $detail->item->distransfer($detail);
                $detail->delete();
            }
        }

        $deportation_good->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function rejection($request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $deportation_good = DeportationGood::findOrFail($id);

        if ($deportation_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        $rows = $request->deportation_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $deportation_good->deportation_good_items()->find($row["id"]);
            $detail->update($row);
        }


        $deportation_good->description = $request->input('description', null);
        $deportation_good->status = 'REJECTED';
        $deportation_good->save();

        $this->DATABASE::commit();
        return response()->json($deportation_good);
    }

    public function validation($request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $deportation_good = DeportationGood::findOrFail($id);

        if ($deportation_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        foreach ($deportation_good->deportation_good_items as $detail) {
            $to = $detail->stockist_from;
            $detail->item->transfer($detail, $detail->unit_amount, null, $to);
            if ((int) $detail->item->stock($to)->total < 0) {
                $this->error($detail->item->part_name . " [$to] OVER STOCK");
            }
        }

        $deportation_good->status = 'VALIDATED';
        $deportation_good->validated_by = $request->user()->id;
        $deportation_good->save();

        $this->DATABASE::commit();
        return response()->json($deportation_good);
    }

    public function revision($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = DeportationGood::findOrFail($id);
        $details = $revise->deportation_good_items;
        foreach ($details as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        if($request->number) {
            $max = (int) DeportationGood::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number' => ($max + 1)]);
        }

        if(!$request->transaction == 'RETURN') $request->merge(['order_mode'=> 'NONE']);

        $deportation_good = DeportationGood::create($request->all());

        $request_order = $revise->request_order;

        $rows = $request->deportation_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $row['valid'] = $row['quantity'];
            $detail = $deportation_good->deportation_good_items()->create($row);

            if (isset($row['request_order_item_id'])) {
                $request_order_item = RequestOrderItem::find($row['request_order_item_id']);
                $detail->request_order_item()->associate($request_order_item);
                $detail->save();
            }

            $to = $deportation_good->transaction == 'RETURN' ? 'NCR' : 'FM';
            $detail->item->transfer($detail, $detail->unit_valid, $to);
        }

        if (strtoupper($deportation_good->order_mode) === 'NONE') {
            $this->reviseRequestOrder($revise, $deportation_good);
        }

        if ($request_order) $deportation_good->request_order()->associate($request_order);
        $deportation_good->status = $revise->status;
        $deportation_good->save();

        $revise->status = 'REVISED';
        $revise->revise_id = $deportation_good->id;
        $revise->save();
        $revise->delete();

        $this->DATABASE::commit();
        return response()->json($deportation_good);
    }
}
