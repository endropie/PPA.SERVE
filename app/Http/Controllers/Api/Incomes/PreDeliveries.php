<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Income\PreDelivery as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Income\PreDelivery as Filters;
use App\Models\Income\PreDelivery;
use App\Traits\GenerateNumber;

class PreDeliveries extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filter)
    {
        switch (request('mode')) {
            case 'all':
                $pre_deliveries = PreDelivery::filter($filter)->latest()->get();
                break;

            case 'datagrid':
                $pre_deliveries = PreDelivery::with(['customer'])->filter($filter)->latest()->get();
                $pre_deliveries->each->setAppends(['is_relationship']);
                break;

            default:
                $pre_deliveries = PreDelivery::with(['customer'])->filter($filter)->latest()->collect();
                $pre_deliveries->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($pre_deliveries);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextPreDeliveryNumber()]);

        $pre_delivery = PreDelivery::create($request->input());

        $rows = $request->pre_delivery_items;
        for ($i=0; $i < count($rows); $i++) {
            // create detail item created!
            $detail = $pre_delivery->pre_delivery_items()->create($rows[$i]);

            $TransRDO = $pre_delivery->transaction === 'RETURN' ? 'RDO.RET' : 'RDO.REG';
            $TransPDO = $pre_delivery->transaction === 'RETURN' ? 'PDO.RET' : 'PDO.REG';

            $detail->item->transfer($detail, $detail->unit_amount, $TransPDO, $TransRDO);
        }

        $this->DATABASE::commit();
        return response()->json($pre_delivery);
    }

    public function show($id)
    {
        $pre_delivery = PreDelivery::with([
            'customer',
            'pre_delivery_items.item.item_units',
            'pre_delivery_items.item.unit',
            'pre_delivery_items.unit'
        ])->withTrashed()->findOrFail($id);

        $pre_delivery->setAppends(['has_relationship']);

        return response()->json($pre_delivery);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $pre_delivery = PreDelivery::findOrFail($id);

        if ($pre_delivery->status == 'VOID') {
            $this->error('The data has been VOID state, is not allowed to be changed!');
        }

        if ($pre_delivery->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be changed!');
        }

        $pre_delivery->update($request->input());

        // Delete old incoming goods items when $request detail rows has not ID
        if($pre_delivery->pre_delivery_items) {
            foreach ($pre_delivery->pre_delivery_items as $detail) {
              // Delete detail of "Request Order"
              $detail->item->distransfer($detail);
              $detail->forceDelete();
            }
        }

        $rows = $request->pre_delivery_items;
        for ($i=0; $i < count($rows); $i++) {
            // create detail item created!
            $detail = $pre_delivery->pre_delivery_items()->create($rows[$i]);

            $TransRDO = $pre_delivery->transaction == 'RETURN' ? 'RDO.RET' : 'RDO.REG';
            $TransPDO = $pre_delivery->transaction == 'RETURN' ? 'PDO.RET' : 'PDO.REG';

            $detail->item->transfer($detail, $detail->unit_amount, $TransPDO, $TransRDO);
            if($detail->item->stock($TransPDO)->total < (0)) $this->error('Data is not allowed to be changed!');
        }

        $this->DATABASE::commit();
        return response()->json($pre_delivery);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $pre_delivery = PreDelivery::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        if ($mode == "VOID") {
            if ($pre_delivery->status == 'VOID') $this->error("The data $pre_delivery->status state, is not allowed to be $mode");

            $rels = $pre_delivery->has_relationship;
            unset($rels["incoming_good"]);
            if ($rels->count() > 0)  $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        }
        else {
            if ($pre_delivery->status != 'OPEN') $this->error("The data $pre_delivery->status state, is not allowed to be $mode");
            if ($pre_delivery->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        }

        if($mode == "VOID") {
            $pre_delivery->status = "VOID";
            $pre_delivery->save();
        }

        foreach ($pre_delivery->pre_delivery_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }
        $pre_delivery->delete();

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }
}
