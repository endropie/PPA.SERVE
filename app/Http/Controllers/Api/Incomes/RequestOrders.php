<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Income\RequestOrder as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Income\RequestOrder as Filters;
use App\Models\Income\RequestOrder;
use App\Traits\GenerateNumber;

class RequestOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        $fields = request('fields');
        $fields = $fields ? explode(',', $fields) : [];

        switch (request('mode')) {
            case 'all':
                $request_orders = RequestOrder::filter($filters)->get();
                break;

            case 'datagrid':
                $request_orders = RequestOrder::with(['user_by', 'customer'])->filter($filters)
                  ->latest()->get();
                $request_orders->each->append(['is_relationship']);
                break;

            default:
                $request_orders = RequestOrder::with(['user_by', 'customer'])
                  ->filter($filters)
                  ->latest()->collect();
                $request_orders->getCollection()->transform(function($item) {
                    $item->append(['is_relationship', 'total_unit_amount', 'total_unit_delivery']);
                    return $item;
                });
                break;
        }

        return response()->json($request_orders);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextRequestOrderNumber()]);

        $request_order = RequestOrder::create($request->all());

        $item = $request->request_order_items;
        for ($i=0; $i < count($item); $i++) {

            $detail = $request_order->request_order_items()->create($item[$i]);

        }

        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function show($id)
    {
        $request_order = RequestOrder::with([
            'customer',
            'request_order_items.item.item_units',
            'request_order_items.unit',
            'delivery_orders'
        ])->withTrashed()->findOrFail($id);

        $request_order->append(['has_relationship','total_unit_amount', 'total_unit_delivery']);

        return response()->json($request_order);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') === 'estimate_updated') return $this->estimate_updated($request, $id);
        if(request('mode') === 'estimate_finished') return $this->estimate_finished($request, $id);
        if(request('mode') === 'closed') return $this->closed($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

        if (request('mode') !== 'referenced' && $request_order->is_relationship == true) {
            $this->error('The data has relationships, Not allowed to be changed');
        }

        if ($request_order->status !== 'OPEN') {
            $this->error('The data has not OPEN state, Not allowed to be changed');
        }

        $request_order->update($request->input());

        if(request('mode') === 'referenced') {
            $this->DATABASE::commit();
            return response()->json($request_order);
        }

        // Delete old incoming goods items when $request detail rows has not ID
        if($request_order->request_order_items) {
          foreach ($request_order->request_order_items as $detail) {
            // Delete detail of "Request Order"
            $detail->item->distransfer($detail);
            $detail->forceDelete();
          }
        }

        $rows = $request->request_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // abort(501, json_encode($fields));
            $detail = $request_order->request_order_items()->create($row);
        }

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        if ($mode == "VOID") {
            if ($request_order->status == 'VOID') $this->error("The data $request_order->status state, is not allowed to be $mode");

            $rels = $request_order->has_relationship;
            unset($rels["incoming_good"]);
            if ($rels->count() > 0)  $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        }
        else {
            if ($request_order->status != 'OPEN') $this->error("The data $request_order->status state, is not allowed to be $mode");
            if ($request_order->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        }

        if($mode == "VOID") {
            $request_order->status = "VOID";
            $request_order->save();
        }

        foreach ($request_order->request_order_items as $detail) {
            // Delete detail of "Request Order"
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        $request_order->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }


    public function closed($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

        if ($request_order->status !== 'OPEN') {
            $this->error('The data has not OPEN state, Not allowed to be CLOSED');
        }

        if ($request_order->trashed()) {
            $this->error('The data failed, Not allowed to be CLOSED');
        }

        if ($request_order->total_unit_delivery == 0) {
            $this->error('delivery undefined, Not allowed to be CLOSED');
        }

        if ($request_order->order_mode == 'NONE') {
            $this->error("The data '$request_order->order_mode' mode , Not allowed to be CLOSED");
        }

        // foreach ($request_order->delivery_orders as $delivery_order) {
        //     if ($delivery_order->status != 'CONFIRMED') $this->error("The data has UNCONFIRMED Delivery, Not allowed to be CLOSED");
        // }

        $this->error("$request_order->order_mode LOLOS");

        $request_order->status = 'CLOSED';
        $request_order->save();

        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function estimate_updated($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

        if (!$request_order->is_estimate) {
            $this->error('The data has not ESTIMATE, Not allowed to be changed');
        }

        if ($request_order->status !== 'OPEN') {
            $this->error('The data has not OPEN state, Not allowed to be changed');
        }

        $request_order = $this->saveEstimate($request, $request_order);

        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function estimate_finished($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

        if (!$request_order->is_estimate) {
            $this->error('The data has not ESTIMATE, Not allowed to be changed');
        }

        if ($request_order->status !== 'OPEN') {
            $this->error('The data has not OPEN state, Not allowed to be changed');
        }

        $request_order = $this->saveEstimate($request, $request_order);

        $request_order->update(['is_estimate' => 0]);

        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    protected function saveEstimate($request, $request_order) {
        $request_order->update($request->input());

        $rows = $request->request_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($row['id'] && $check = $request_order->request_order_items()->find($row['id'])) {
                if ($check->quantity > $row['quantity']) $this->error('Quantity Invalid!');
            }
            $detail = $request_order->request_order_items()->updateOrCreate(['id'=> $row['id']], $row);
            $detail->item->distransfer($detail);
        }

        return $request_order->fresh();
    }
}
