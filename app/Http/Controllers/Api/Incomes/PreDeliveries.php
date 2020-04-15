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
                $pre_deliveries->each->append(['is_relationship']);
                break;

            default:
                $pre_deliveries = PreDelivery::with(['created_user','customer'])->filter($filter)->latest()->collect();
                $pre_deliveries->getCollection()->transform(function($item) {
                    $item->append(['is_relationship','summary_items','summary_verifications']);
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

            $stockist = $pre_delivery->transaction === 'RETURN' ? 'PDO.RET' : 'PDO.REG';

            $detail->item->transfer($detail, $detail->unit_amount, $stockist);

            $totals = $detail->item->totals;
            if ($pre_delivery->order_mode != "PO" && round($totals[$stockist]) > round($totals["*"])) {
                $request->validate(["pre_delivery_items.$i.quantity" => "not_in:$detail->quantity"]);
            }
        }

        $schedules = collect($request->schedules)->map(function($item) {
            if ($item['status'] != "OPEN") $this->error($item['number'] ." has not OPEN state. CREATED FAILED!");
            return $item["id"];
        });

        $pre_delivery->schedules()->sync($schedules);

        $this->DATABASE::commit();
        return response()->json($pre_delivery);
    }

    public function show($id)
    {
        $pre_delivery = PreDelivery::with([
            'customer',
            'schedules',
            'pre_delivery_items.item.item_units',
            'pre_delivery_items.item.unit',
            'pre_delivery_items.unit',
            'pre_delivery_items.outgoing_verifications'
        ])->withTrashed()->findOrFail($id);

        $pre_delivery->append(['has_relationship']);

        return response()->json($pre_delivery);
    }

    public function update(Request $request, $id)
    {
        if (request('mode') == 'revision') return $this->revision($request, $id);
        if (request('mode') == 'close') return $this->close($request, $id);

        $this->DATABASE::beginTransaction();

        $pre_delivery = PreDelivery::findOrFail($id);

        if ($pre_delivery->status == 'CLOSED') {
            $this->error('PDO has been CLOSED state, is not allowed to be changed!');
        }

        if ($pre_delivery->trashed()) {
            $this->error('PDO has trashed, is not allowed to be changed!');
        }

        if ($pre_delivery->is_relationship == true) {
            $this->error('PDO has relationships, is not allowed to be changed!');
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


            $stockist = $pre_delivery->transaction === 'RETURN' ? 'PDO.RET' : 'PDO.REG';
            $detail->item->transfer($detail, $detail->unit_amount, $stockist);

            $totals = $detail->item->totals;
            if ($pre_delivery->order_mode != "PO" && round($totals[$stockist]) > round($totals["*"])) {
                $request->validate(["pre_delivery_items.$i.quantity" => "not_in:$detail->quantity"]);
            }
        }

        $schedules = collect($request->schedules)->map(function($item) {
            if ($item["status"] != "OPEN") $this->error($item["number"] ." has not OPEN state. UPDATE FAILED!");
            return $item["id"];
        });
        $pre_delivery->schedules()->sync($schedules);


        $this->DATABASE::commit();
        return response()->json($pre_delivery);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $pre_delivery = PreDelivery::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        if ($mode == "VOID") {
            if ($pre_delivery->status == 'VOID') $this->error("PDO $pre_delivery->status state, is not allowed to be $mode");

            $rels = $pre_delivery->has_relationship;
            unset($rels["incoming_good"]);
            if ($rels->count() > 0)  $this->error("PDO has RELATIONSHIP, is not allowed to be $mode");
        }
        else {
            if ($pre_delivery->status != 'OPEN') $this->error("PDO $pre_delivery->status state, is not allowed to be $mode");
            if ($pre_delivery->is_relationship) $this->error("PDO has RELATIONSHIP, is not allowed to be $mode");
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

    public function revision($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = PreDelivery::findOrFail($id);

        if ($revise->status == 'CLOSED') {
            $this->error('PDO has been CLOSED state, is not allowed to be Revised!');
        }

        if ($revise->trashed()) {
            $this->error('PDO has trashed, is not allowed to be Revised!');
        }

        foreach ($revise->pre_delivery_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->outgoing_verifications->each(function($verification) {
                $verification->item->distransfer($verification);
                $verification->delete();
            });
            $detail->delete();
        }

        if($request->number) {
            $max = (int) PreDelivery::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number' => ($max + 1)]);
        }

        $pre_delivery = PreDelivery::create($request->all());
        if($request->number) {
            $max = (int) PreDelivery::where('number', $request->number)->max('revise_number');
            $pre_delivery->revise_number = ($max + 1);
            $pre_delivery->save();
        }

        $rows = $request->pre_delivery_items;
        for ($i=0; $i < count($rows); $i++) {
            // create detail item created!
            $detail = $pre_delivery->pre_delivery_items()->create($rows[$i]);

            $stockist = $pre_delivery->transaction === 'RETURN' ? 'PDO.RET' : 'PDO.REG';
            $detail->item->transfer($detail, $detail->unit_amount, $stockist);
            $totals = $detail->item->totals;

            if ($pre_delivery->order_mode != "PO" && round($totals[$stockist]) > round($totals["*"])) {
                $request->validate(["pre_delivery_items.$i.quantity" => "not_in:$detail->quantity"]);
            }

            $verifications = $rows[$i]['outgoing_verifications'];

            foreach ($verifications as $verification) {
                $verification = array_merge($verification, [
                    'item_id' => $detail->item_id,
                    'unit_id' => $detail->unit_id,
                    'unit_rate' => $detail->unit_rate,
                ]);
                $outgoing_verification = $detail->outgoing_verifications()->create($verification);
                $outgoing_verification->item->transfer($outgoing_verification, $outgoing_verification->unit_amount, 'VDO');
                $outgoing_verification->pre_delivery_item()->associate($detail);
                $outgoing_verification->created_by = $verification['created_by'];
                $outgoing_verification->created_at = $verification['created_at'];
                $outgoing_verification->save();
            }
            $detail->calculate();

            if (round($detail->unit_amount) < round($detail->amount_verification)) {
                $request->validate(["pre_delivery_items.$i.quantity" => "not_in:$detail->quantity"]);
            }
        }

        $revise->status = 'REVISED';
        $revise->revise_id = $pre_delivery->id;
        $revise->save();
        $revise->delete();

        $this->DATABASE::commit();
        return response()->json($pre_delivery);
    }

    public function close($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $pre_delivery = PreDelivery::findOrFail($id);

        if ($pre_delivery->trashed()) {
            $this->error("PDO has trashed. Not Allowed to CLOSED" );
        }

        if ($pre_delivery->status === 'CLOSED') {
            $this->error("status has CLOSED. Not Allowed to CLOSED" );
        }

        foreach ($pre_delivery->pre_delivery_items as $detail) {

            if (round($detail->amount_verification) > round($detail->unit_amount)) {
                $this->error("[". $detail->item->part_name ."] unit verification failed. Not Allowed to CLOSED" );
            }

            $detail->item->distransfer($detail);
            $detail->calculate();

            $stockist = $pre_delivery->transaction === 'RETURN' ? 'PDO.RET' : 'PDO.REG';
            $detail->item->transfer($detail, $detail->amount_verification, $stockist);
        }

        $pre_delivery->status = 'CLOSED';
        $pre_delivery->save();

        $this->DATABASE::commit();
        return response()->json($pre_delivery);
    }
}
