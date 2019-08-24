<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Filters\Warehouse\OutgoingGoodVerification as Filters;
use App\Http\Requests\Warehouse\OutgoingGoodVerification as Request;
use App\Http\Controllers\ApiController;
use App\Models\Warehouse\OutgoingGoodVerification;
use App\Models\Income\PreDeliveryItem;
use App\Traits\GenerateNumber;

class OutgoingGoodVerifications extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $outgoing_good_verifications = OutgoingGoodVerification::with(['item','unit'])
                  ->filter($filters)
                  ->latest()->get();
                break;

            case 'datagrid':
                $outgoing_good_verifications = OutgoingGoodVerification::with(['item','unit'])
                ->filter($filters)
                ->latest()->get();
                $outgoing_good_verifications->each->setAppends(['has_relationship']);
                break;

            default:
                $outgoing_good_verifications = OutgoingGoodVerification::with(['item','unit'])
                ->filter($filters)
                ->latest()->collect();
                $outgoing_good_verifications->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($outgoing_good_verifications);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        foreach ($request->outgoing_good_verifications as $row) {
            if($row['quantity'] > 0) {
                $detail = OutgoingGoodVerification::create($row);

                if(!$detail->pre_delivery_item) $this->error('Data is not allowed to be created!');

                $STOCKIST = $detail->pre_delivery_item->pre_delivery->transaction == 'RETURN' ? 'PDO.RET' : 'PDO.REG';
                if($detail->item->stock($STOCKIST)->total < ($detail->unit_amount - 0.1)) $this->error('Data is not allowed to be created!');
                $detail->item->transfer($detail, $detail->unit_amount, 'VDO', $STOCKIST);
            }
        }

        $this->DATABASE::commit();
        return response()->json(['error' => false, 'message' => 'Items created']);
    }

    public function show($id)
    {
        $outgoing_good_verification = OutgoingGoodVerification::with([
            'item.item_units',
            'unit',
        ])->withTrashed()->findOrFail($id);

        $outgoing_good_verification->setAppends(['unit_amount','has_relationship']);

        return response()->json($outgoing_good_verification);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $detail = OutgoingGoodVerification::findOrFail($id);


        if ($detail->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be changed');
        }

        // $this->error($request->input());

        $detail->item->distransfer($detail);

        $detail->update($request->input());

        if($detail->item->stock('PDO')->total < ($detail->unit_amount - 0.1)) $this->error('Data is not allowed to be updated!');
        $detail->item->transfer($detail, $detail->unit_amount, 'VDO', 'PDO');

        $this->DATABASE::commit();
        return response()->json($detail);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $detail = OutgoingGoodVerification::findOrFail($id);

        if ($detail->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be deleted');
        }

        $detail->item->distransfer($detail);
        $detail->forceDelete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
