<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\OpnameStock as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Warehouse\OpnameStock as Filters;
use App\Models\Warehouse\OpnameStock;
use App\Traits\GenerateNumber;

class OpnameStocks extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $opname_stocks = OpnameStock::filter($filters)->get();
                break;

            case 'datagrid':
                $opname_stocks = OpnameStock::filter($filters)->latest()->get();
                $opname_stocks->each->setAppends(['is_relationship']);
                break;

            default:
                $opname_stocks = OpnameStock::filter($filters)->latest()->collect();
                $opname_stocks->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($opname_stocks);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextOpnameStockNumber()]);

        $opname_stock = OpnameStock::create($request->all());

        $rows = $request->opname_stock_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $opname_stock->opname_stock_items()->create($row);
            $label = $detail->item->part_name ?? $detail->item->part_number ?? $detail->item->id;
            if (!$detail->item->enable) $this->error("PART [". $label . "] DISABLED");

        }

        $this->error('LOLOS');

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($opname_stock);
    }

    public function show($id)
    {
        $opname_stock = OpnameStock::withTrashed()->with([
            'opname_stock_items.item.item_units',
            'opname_stock_items.unit'
        ])->findOrFail($id);

        $opname_stock->setAppends(['is_relationship','has_relationship']);

        return response()->json($opname_stock);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') === 'validation') return $this->validation($request, $id);
        if(request('mode') === 'revision') return $this->revision($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname_stock = OpnameStock::findOrFail($id);

        if ($opname_stock->status != "OPEN") $this->error("$opname_stock->number is not OPEN state, is not allowed to be changed");
        if ($opname_stock->is_relationship) $this->error("$opname_stock->number has relationships, is not allowed to be changed");

        $opname_stock->update($request->input());

        // Before Update Force delete opname stocks items
        $opname_stock->opname_stock_items()->forceDelete();

        // Update opname stocks items
        $rows = $request->opname_stock_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Update or Create detail row
            $detail = $opname_stock->opname_stock_items()->create($row);
            $label = $detail->item->part_name ?? $detail->item->part_number ?? $detail->item->id;
            if (!$detail->item->enable) $this->error("PART [". $label . "] DISABLED");
        }

        $this->DATABASE::commit();
        return response()->json($opname_stock);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname_stock = OpnameStock::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($opname_stock->is_relationship) $this->error("$opname_stock->number has relationship, is not allowed to be $mode");
        if($mode == "DELETED" && $opname_stock->status != 'OPEN') $this->error("The data $opname_stock->status state, is not allowed to be $mode");

        if($mode == 'VOID') {
            $opname_stock->status = "VOID";
            $opname_stock->save();
        }

        if($details = $opname_stock->opname_stock_items) {
            foreach ($details as $detail) {
                $detail->item->distransfer($detail);
                $detail->delete();
            }
        }

        $opname_stock->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function validation($request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname_stock = OpnameStock::findOrFail($id);

        if ($opname_stock->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        foreach ($opname_stock->opname_stock_items as $detail) {
            // Calculate stock on "validation" Opname Stock!
            $detail->item->transfer($detail, $detail->unit_amount, $detail->stockist);
        }

        $opname_stock->status = 'VALIDATED';
        $opname_stock->save();

        $this->DATABASE::commit();
        return response()->json($opname_stock);
    }

    public function revision($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = OpnameStock::findOrFail($id);
        $details = $revise->opname_stock_items;
        foreach ($details as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        if($request->number) {
            $max = (int) OpnameStock::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number' => ($max + 1)]);
        }

        $opname_stock = OpnameStock::create($request->all());

        $rows = $request->opname_stock_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $detail = $opname_stock->opname_stock_items()->create($row);

            $detail->item->transfer($detail, $detail->unit_amount, $detail->stockist);
        }


        $opname_stock->status = 'VALIDATED';
        $opname_stock->save();

        $revise->status = 'REVISED';
        $revise->revise_id = $opname_stock->id;
        $revise->save();
        $revise->delete();

        $this->DATABASE::commit();
        return response()->json($opname_stock);
    }
}
