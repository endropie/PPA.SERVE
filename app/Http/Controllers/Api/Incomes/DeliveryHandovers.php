<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Filter;
use App\Http\Requests\Request as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\DeliveryHandover;
use App\Models\Income\DeliveryOrder;
use App\Models\Income\RequestOrder;
use App\Traits\GenerateNumber;

class DeliveryHandovers extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter)
    {
        $fields = request('fields');
        $fields = $fields ? explode(',', $fields) : [];

        switch (request('mode')) {
            case 'all':
                $delivery_handovers = DeliveryHandover::filter($filter)->get();
                break;

            case 'datagrid':
                $delivery_handovers = DeliveryHandover::with(['created_user'])->filter($filter)
                  ->latest()->get();
                // $delivery_handovers->each->append(['is_relationship']);
                break;

            default:
                $delivery_handovers = DeliveryHandover::with(['created_user', 'customer'])
                  ->filter($filter)
                  ->latest()->collect();
                $delivery_handovers->getCollection()->transform(function($item) {
                    // $item->append(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($delivery_handovers);
    }

    public function store(Request $request)
    {

        $request->validate([
            'customer_id' => 'required',
            'date' => 'required',
            'delivery_orders' => 'required|array',
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_handover = DeliveryHandover::create($request->merge([
            'number' => $this->getNextDeliveryHandoverNumber(),
            'date' => $request->date ?? now(),
        ])->all());

        foreach ($request->input('delivery_orders') as $row) {
            $delivery_order = DeliveryOrder::whereNull('delivery_handover_id')->find($row['id']);

            if (!$delivery_order) return $this->error('Delivery undefined! [ID: #'. $row['id'] .']');
            if ($delivery_order->status !== 'CONFIRMED') return $this->error('Delivery not confirmed! ['. $delivery_order->fullnumber .']');
            if ($delivery_order->is_internal) $this->error('Delivery is internal! ['. $delivery_order->fullnumber .']');

            $delivery_order->delivery_handover_id = $delivery_handover->id;
            $delivery_order->save();
        }

        $delivery_handover->setCommentLog("Delivery Handover [$delivery_handover->fullnumber] has been Created");

        $this->DATABASE::commit();
        return response()->json($delivery_handover);
    }

    public function show($id, Filter $filter)
    {
        $delivery_handover = DeliveryHandover::with(['delivery_orders'])->filter($filter)->findOrFail($id);

        $delivery_handover->setAppends(['has_relationship']);

        $delivery_handover->delivery_orders->each->setAppends(['confirmed_user', 'fullnumber']);

        return response()->json($delivery_handover);
    }

    public function update($id, Request $request)
    {

        $request->validate([
            'id' => 'required',
            'customer_id' => 'required',
            'date' => 'required',
            'order_mode' => 'required',
            'invoice_mode' => 'required',
            'customer_id' => 'required',
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_handover = DeliveryHandover::findOrFail($id);

        // if ($delivery_handover->status != 'OPEN')  $this->error('Delivery Handover is not "OPEN" state. Update Failed!');
        if ($delivery_handover->accurate_model_id)  $this->error('Delivery Handover has generated. Update Failed!');
        if ($delivery_handover->service_model_id)  $this->error('Delivery Handover has generated. Update Failed!');

        $delivery_handover->update($request->all());

        $delivery_handover->delivery_orders()->update(['delivery_handover_id' => null]);


        $request->validate(['delivery_orders' => 'required|array']);

        foreach ($request->input('delivery_orders') as $row) {
            $delivery_order = DeliveryOrder::where(function ($q) use ($id) {
                    return $q->whereNull('delivery_handover_id')->orWhere('delivery_handover_id', $id);
                })
                ->find($row['id']);

            if (!$delivery_order) return $this->error('Delivery undefined! [ID: '. $row['id'] .']');
            if ($delivery_order->status !== 'CONFIRMED') return $this->error('Delivery not confirmed! [SJDO: '. $delivery_order->fullnumber .']');
            if ($delivery_order->is_internal) $this->error('Delivery is internal! ['. $delivery_order->fullnumber .']');

            $delivery_order->delivery_handover_id = $delivery_handover->id;
            $delivery_order->save();
        }

        $delivery_handover->setCommentLog("Delivery Handover [$delivery_handover->fullnumber] has been Updated");

        $this->DATABASE::commit();
        return response()->json($delivery_handover);
    }

    public function destroy($id)
    {

        $delivery_handover = DeliveryHandover::findOrFail($id);

        $mode = request('mode', 'DELETED');

        // if ($delivery_handover->status !== 'INVOICED') $this->error('The data has INVOICED state, Not allowed to be DELETED');

        $delivery_handover->delete();

        $delivery_handover->setCommentLog("Delivery Handover [$delivery_handover->fullnumber] has been $mode");

        return response()->json(['success' => true]);
    }

}
