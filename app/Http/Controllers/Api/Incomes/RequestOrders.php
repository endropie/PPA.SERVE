<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request as BaseRequest;
use App\Http\Requests\Income\RequestOrder as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Income\RequestOrder as Filters;
use App\Models\Income\AccInvoice;
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
                $request_orders = RequestOrder::with(['created_user', 'customer'])->filter($filters)
                  ->latest()->get();
                $request_orders->each->append(['is_relationship']);
                break;

            default:
                $request_orders = RequestOrder::with(['created_user', 'customer'])
                  ->filter($filters)
                  ->latest()->collect();
                $request_orders->getCollection()->transform(function($item) {
                    $item->append(['is_relationship', 'total_unit_amount', 'total_unit_delivery', 'delivery_counter']);
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
            'request_order_items.incoming_good_item',
            'request_order_items.delivery_order_items',
            'delivery_orders',
            'acc_invoices'
        ])->withTrashed()->findOrFail($id);

        $request_order->append(['has_relationship','total_unit_amount', 'total_unit_delivery']);

        // $request_order->request_order_items->each->append('order_lots');

        return response()->json($request_order);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') === 'calculate') return $this->calculate($request, $id);
        if(request('mode') === 'close') return $this->close($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

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
            $rows = collect($request->request_order_items);
            foreach ($request_order->request_order_items as $detail) {
                $detail->item->distransfer($detail);

                ## Find except row from old Details.
                if (!$rows->contains('id', $detail->id)) {
                    $name = ($detail->item->part_name) ?? ('#'.$detail->id);
                    if ($detail->amount_delivery > 0) $this->error("Part [$name] is not allowed to removed!");
                    $detail->forceDelete();
                }
            }
        }

        $rows = $request->request_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $old = $request_order->request_order_items()->find($row['id']);
            $detail = $request_order->request_order_items()->updateOrCreate(['id' => $row['id']], $row);

            if ($old && $old->delivery_order_items->count()) {
                $request->validate(["request_order_items.$i.item_id" => "in:".$old['item_id']]);
            }
            if (round($detail->amount_delivery) > round($detail->unit_amount)) {
                $request->validate(["request_order_items.$i.quantity" => "not_in:".$row['quantity']]);
            }
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

    public function close($request, $id)
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

        $request_order->status = 'CLOSED';
        $request_order->save();

        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function calculate($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

        $request_order->request_order_items->each->calculate();

        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function createInvoice($id, BaseRequest $request)
    {
        $request->validate([
            'date' => 'required',
            'delivery_orders.*.id' => 'required'
        ]);

        // return response()->json($request->toArray(),501);
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);
        $invoice = $request_order->acc_invoices()->create([
            'number' => $this->getNextAccInvoiceNumber(),
            'date' => $request->date ?? now(),
        ]);

        foreach ($request->input('delivery_orders') as $row) {
            $delivery_order = $request_order->delivery_orders()->find($row['id']);

            if (!$delivery_order) return $this->error('Delivery undefined! [ID: '. $row['id'] .']');
            if ($delivery_order->status !== 'CONFIRMED') return $this->error('Delivery not confirmed! [SJDO: '. $delivery_order->fullnumber .']');

            $delivery_order->acc_invoice()->associate($invoice);
            $delivery_order->save();
        }

        $response = $invoice->accurate()->push();

        if ($invoice->request_order->customer->invoice_mode == 'SEPARATE') {

            $invoice2 = $request_order->acc_invoices()->create([
                'number' => $invoice->number . ".JASA",
                'date' => $request->date ?? now(),
            ]);
            $invoice2->material_invoice()->associate($invoice);
            $invoice2->save();

            $response2 = $invoice2->accurate()->push();

            if (!$response2['s']) {
                $this->DATABASE::rollback();
                return response()->json(['message' => $response2['d'], 'success' => $response2['s']], 501);
            }
        }

        if (!$response['s']) {
            $this->DATABASE::rollback();
            return response()->json(['message' => $response['d'], 'success' => $response['s']], 501);
        }

        $this->DATABASE::commit();
        return response()->json($response);
    }

    public function forgetInvoice ($id)
    {
        $invoice = AccInvoice::findOrFail($id);
        $forget = $invoice->accurate()->forget();

        if ($invoice2 = $invoice->service_invoice) {
            $invoice2->accurate()->forget();
            $invoice2->delete();
        }

        // if ($invoice->accurate_service_model_id)
        // {
        //     $service = $invoice->service();
        //     $service->setAccuratePrimaryKeyAttribute('accurate_service_model_id');
        //     $service->accurate()->forget();
        // }

        $invoice->delete();

        return $forget;
    }

    public function showInvoice($id)
    {
        $invoice = AccInvoice::with(['request_order.customer'])->findOrFail($id);

        $invoice = $invoice->setAppends(['deliveries']);

        return response()->json($invoice);
    }

}
