<?php

namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\PackingLoad as Filters;
use App\Http\Requests\Factory\PackingLoad as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\PackingLoad;
use App\Traits\GenerateNumber;

class PackingLoads extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filter)
    {
        switch (request('mode')) {
            case 'all':
                $packing_loads = PackingLoad::filter($filter)->get();
                break;

            case 'datagrid':
                $packing_loads = PackingLoad::with([
                    'customer',
                    'packing_load_items.item',
                    'packing_load_items.unit',
                ])->filter($filter)->latest()->orderBy('id', 'DESC')->get();

                break;

            default:
                $packing_loads = PackingLoad::with([
                    'customer',
                    'created_user',
                    'packing_load_items.item',
                    'packing_load_items.unit',
                ])->filter($filter)->latest()->orderBy('id', 'DESC')->collect();

                $packing_loads->getCollection()->transform(function ($row) {
                    return $row;
                });
                break;
        }

        return response()->json($packing_loads);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if (!$request->number) $request->merge(['number' => $this->getNextPackingLoadNumber()]);

        ## Create the PackingLoad.
        $packing_load = PackingLoad::create($request->all());

        foreach ($request->packing_load_items as $row) {

            ## Create the PackingLoad item. Note: with "hasOne" Relation.
            $detail = $packing_load->packing_load_items()->create($row);

            ## Calculate stock on after the PackingLoad items Created!
            $detail->item->transfer($detail, $detail->unit_amount, 'FG', 'PFG');
        }

        $packing_load->setCommentLog("PackingLoad [$packing_load->fullnumber] has been Created.");

        $this->DATABASE::commit();
        return response()->json($packing_load);
    }

    public function show($id)
    {

        $packing_load = PackingLoad::with([
            'customer',
            'packing_load_items.item',
            'packing_load_items.unit',
            'packing_load_items.item.item_units'
        ])->withTrashed()->findOrFail($id);

        return response()->json($packing_load);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $packing_load = PackingLoad::findOrFail($id);

        if ($packing_load->is_relationship) $this->error('The data has RELATIONSHIP, is not allowed to be updated!');
        if ($packing_load->status != "OPEN") $this->error("The data on $packing_load->satus state , is not allowed to be updated!");

        foreach ($packing_load->packing_load_items as $oldDetail) {
            ## Calculate stock on before the PackingLoad items (old) remove!
            $oldDetail->item->distransfer($oldDetail);
            $oldDetail->forceDelete();
        }

        $packing_load->update($request->all());

        foreach ($request->packing_load_items as $row) {
            ## Create the PackingLoad item. Note: with "hasOne" Relation.
            $detail = $packing_load->packing_load_items()->create($row);

            ## Calculate stock on after the PackingLoad items Created!
            $detail->item->transfer($detail, $detail->unit_amount, 'FG', 'PFG');
        }

        $packing_load->setCommentLog("PackingLoad [$packing_load->fullnumber] has been Updated.");

        $this->DATABASE::commit();
        return response()->json($packing_load);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();
        $packing_load = PackingLoad::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if ($packing_load->is_relationship) $this->error("[$packing_load->number] has RELATIONSHIP, is not allowed to be $mode!");
        if ($mode == "DELETED" && $packing_load->status != "OPEN") $this->error("[$packing_load->number] $packing_load->status state, is not allowed to be $mode!");

        $packing_load->status = $mode;
        $packing_load->save();

        foreach ($packing_load->packing_load_items as $detail) {

            $to = 'FG';
            if (round($detail->item->getTotalStockist($to)) < round($detail->unit_valid)) {
                $name = $detail->item->part_name ." - ". $detail->item->part_subname;
                $this->error("Unit Quantity Part [$name] has Failed to $mode");
            }
            ## Calculate Stok Before deleting
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        $packing_load->delete();

        $packing_load->setCommentLog("PackingLoad [$packing_load->fullnumber] has been $mode.");

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
