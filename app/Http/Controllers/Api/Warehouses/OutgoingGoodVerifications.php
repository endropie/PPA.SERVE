<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Filters\Warehouse\OutgoingGoodVerification as Filters;
use App\Http\Requests\Warehouse\OutgoingGoodVerification as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\PreDeliveryItem;
use App\Models\Warehouse\OutgoingGoodVerification;
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
                $outgoing_good_verifications->each->append(['has_relationship']);
                break;

            default:
                $outgoing_good_verifications = OutgoingGoodVerification::with(['created_user','item','unit','pre_delivery_item.pre_delivery'])
                ->filter($filters)
                ->latest()->collect();
                $outgoing_good_verifications->getCollection()->transform(function($item) {
                    $item->append(['is_relationship', 'pre_delivery_number']);
                    return $item;
                });
                break;
        }

        return response()->json($outgoing_good_verifications);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        foreach ($request->outgoing_good_verifications as $key => $row) {
            if(round($row['quantity']) <= 0) continue;

            // $this->error($row['pre_delivery_item_id']);
            if ($pre_delivery_item = PreDeliveryItem::find($row['pre_delivery_item_id'])) {

                if ($pre_delivery_item->pre_delivery->status != 'OPEN') {
                    $this->error('PDO has not OPEN state, Not allowed to be created');
                }
                $detail = $pre_delivery_item->outgoing_verifications()->create(array_merge($row, ['date' => $request->date]));
                $detail->item->transfer($detail, $detail->unit_amount, 'VDO');
                $pre_delivery_item->calculate();
                if (round($pre_delivery_item->unit_amount) < round($pre_delivery_item->amount_verification)) {
                    $request->validate(["outgoing_good_verifications.$key.quantity" => "required|not_in:".$row['quantity']]);
                }
            }
            else $request->validate(["outgoing_good_verifications.$key.pre_delivery_item_id" => "required|not_in:".$row['pre_delivery_item_id']]);
        }

        $this->DATABASE::commit();
        return response()->json(['error' => false, 'message' => 'Items created']);
    }

    public function show($id)
    {
        $outgoing_good_verification = OutgoingGoodVerification::with([
            'pre_delivery_item',
            'item.item_units',
            'unit',
        ])->withTrashed()->findOrFail($id);

        $outgoing_good_verification->append(['unit_amount','has_relationship']);

        return response()->json($outgoing_good_verification);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $detail = OutgoingGoodVerification::findOrFail($id);

        if ($detail->pre_delivery_item->pre_delivery->status != 'OPEN') {
            $this->error('PDO has not OPEN state, is not allowed to be changed');
        }
        if ($detail->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be changed');
        }

        $detail->item->distransfer($detail);

        $detail->update($request->input());
        $detail->item->transfer($detail, $detail->unit_amount, 'VDO');
        $detail->pre_delivery_item->calculate();
        if (round($detail->pre_delivery_item->unit_amount) < round($detail->pre_delivery_item->amount_verification)) {
            $request->validate(["quantity" => "required|not_in:".$request->input('quantity')]);
        }

        $this->DATABASE::commit();
        return response()->json($detail);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $detail = OutgoingGoodVerification::findOrFail($id);

        if ($detail->pre_delivery_item->pre_delivery->status != 'OPEN') {
            $this->error('PDO has not OPEN state, is not allowed to be changed');
        }
        if ($detail->is_relationship == true) {
            $this->error('The data has relationships, is not allowed to be deleted');
        }

        $pre_delivery_item = $detail->pre_delivery_item;

        $detail->item->distransfer($detail);
        $detail->forceDelete();
        $pre_delivery_item->calculate();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
