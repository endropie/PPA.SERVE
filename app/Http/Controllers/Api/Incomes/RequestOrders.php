<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request as BaseRequest;
use App\Http\Requests\Income\RequestOrder as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Income\RequestOrder as Filter;
use App\Filters\Income\RequestOrderItem as FilterItem;
use App\Models\Income\AccInvoice;
use App\Models\Income\RequestOrder;
use App\Models\Income\RequestOrderItem;
use App\Traits\GenerateNumber;

class RequestOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $request_orders = RequestOrder::filter($filter)->latest()->get();
                break;

            case 'datagrid':
                $request_orders = RequestOrder::with(['created_user', 'customer'])->filter($filter)
                    ->latest()->get();
                $request_orders->each->append(['delivery_counter']);
                break;

            default:
                $request_orders = RequestOrder::with(['created_user'])
                    ->filter($filter)
                    ->latest()->collect();
                $request_orders->getCollection()->transform(function ($item) {
                    $item->customer = $item->customer()->first()->only(['id', 'name', 'code']);
                    $item->append(['delivery_counter']);
                    return $item;
                });
                break;
        }

        return response()->json($request_orders);
    }


    public function items(FilterItem $filter)
    {
        $request_order_items = RequestOrderItem::filter($filter)->latest()->get();

        if ($date = request('delivery_date')) {
            $request_order_items->map(function ($detail) use ($date) {
                $detail->item->item_units;
                $detail->item->amount_delivery = [
                    "FG" => $detail->item->totals["FG"],
                    "VERIFY" => $detail->item->amount_delivery_verify($date),
                    "TASK.REG" => $detail->item->amount_delivery_task($date, 'REGULER'),
                    "TASK.RET" => $detail->item->amount_delivery_task($date, 'RETURN'),
                    "LOAD.REG" => $detail->item->amount_delivery_load($date, 'REGULER'),
                    "LOAD.RET" => $detail->item->amount_delivery_load($date, 'RETURN')
                ];
                return $detail;
            });
        }

        return response()->json($request_order_items);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();
        if (!$request->number) $request->merge(['number' => $this->getNextRequestOrderNumber()]);

        $request_order = RequestOrder::create($request->all());

        $item = $request->request_order_items;
        for ($i = 0; $i < count($item); $i++) {

            $detail = $request_order->request_order_items()->create($item[$i]);
        }

        $request_order->setCommentLog("Sales Order [$request_order->fullnumber] has been created!");

        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function show($id)
    {
        $request_order = RequestOrder::with([
            'customer',
            'request_order_items.item.item_units',
            'request_order_items.unit',
            // 'request_order_items.incoming_good_item',
            // 'request_order_items.delivery_order_items',
            // 'delivery_orders',
            // 'acc_invoices'
        ])->withTrashed()->findOrFail($id);

        $request_order->append(['has_relationship', 'total_unit_amount', 'total_unit_delivery']);
        $request_order->request_order_items->each->append('lots');

        ## resource return as json
        $request_order->delivery_orders = $request_order->delivery_orders()->get()->map(function ($delivery, $key) {
            return $delivery->only(['id', 'fullnumber', 'status']);
        });


        return response()->json($request_order);
    }

    public function update(Request $request, $id)
    {
        if (request('mode') === 'calculate') return $this->calculate($request, $id);
        if (request('mode') === 'close') return $this->close($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

        if ($request_order->status !== 'OPEN') $this->error('The data has not OPEN state, Not allowed to be changed');

        if ($request_order->acc_invoice_id) $this->error("The data has Invoice Collect, is not allowed to be changed!");

        $request_order->update($request->input());

        if (request('mode') === 'referenced') {
            $this->DATABASE::commit();
            return response()->json($request_order);
        }

        // Delete old incoming goods items when $request detail rows has not ID
        if ($request_order->request_order_items) {
            $rows = collect($request->request_order_items);
            foreach ($request_order->request_order_items as $detail) {
                $detail->item->distransfer($detail);

                ## Find except row from old Details.
                if (!$rows->contains('id', $detail->id)) {
                    $name = ($detail->item->part_name) ?? ('#' . $detail->id);
                    if ($detail->amount_delivery > 0) $this->error("Part [$name] is not allowed to removed!");
                    $detail->forceDelete();
                }
            }
        }

        $rows = $request->request_order_items;
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $old = $request_order->request_order_items()->find($row['id']);
            $detail = $request_order->request_order_items()->updateOrCreate(['id' => $row['id']], $row);

            if ($old && $old->delivery_order_items->count()) {
                $request->validate(["request_order_items.$i.item_id" => "in:" . $old['item_id']]);
            }
            if (round($detail->amount_delivery) > round($detail->unit_amount)) {
                $request->validate(["request_order_items.$i.quantity" => "not_in:" . $row['quantity']]);
            }
        }

        $request_order->setCommentLog("Sales Order [$request_order->fullnumber] has been updated!");

        $this->DATABASE::commit();
        return response()->json($request_order);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $request_order = RequestOrder::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');


        if ($request_order->acc_invoice_id) $this->error("The data has Invoice Collect, is not allowed to be $mode!");

        if ($mode == "VOID") {
            if ($request_order->status == 'VOID') $this->error("The data $request_order->status state, is not allowed to be $mode");

            $rels = $request_order->has_relationship;
            unset($rels["incoming_good"]);
            if ($rels->count() > 0)  $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        } else {
            if ($request_order->status != 'OPEN') $this->error("The data $request_order->status state, is not allowed to be $mode");
            if ($request_order->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        }

        if ($mode == "VOID") {
            $request_order->status = "VOID";
            $request_order->save();
        }

        foreach ($request_order->request_order_items as $detail) {
            // Delete detail of "Request Order"
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        $request_order->delete();

        $action = ($mode == "VOID") ? 'voided' : 'deleted';
        $request_order->setCommentLog("Sales Order [$request_order->fullnumber] has been $action !");

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

        $request_order->setCommentLog("Sales Order [$request_order->fullnumber] has been closed!");

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

    public function setLockDetail($id)
    {
        $this->DATABASE::beginTransaction();

        $request_order_item = RequestOrderItem::findOrFail($id);

        if ($request_order_item->request_order->status !== 'OPEN') $this->error("The data has not OPEN status, is not allowed to be LOCK!");

        $request_order_item->is_autoload = 1;
        $request_order_item->save();

        $this->DATABASE::commit();
        return response()->json($request_order_item);
    }

    public function setUnlockDetail($id)
    {
        $this->DATABASE::beginTransaction();

        $request_order_item = RequestOrderItem::findOrFail($id);

        if ($request_order_item->request_order->status !== 'OPEN') $this->error("The data has not OPEN status, is not allowed to be LOCK!");

        $request_order_item->is_autoload = 0;
        $request_order_item->save();

        $this->DATABASE::commit();
        return response()->json($request_order_item);
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

            if (!$delivery_order) return $this->error('Delivery undefined! [ID: ' . $row['id'] . ']');
            if ($delivery_order->status !== 'CONFIRMED') return $this->error('Delivery not confirmed! [SJDO: ' . $delivery_order->fullnumber . ']');

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

    public function forgetInvoice($id)
    {
        $invoice = AccInvoice::findOrFail($id);
        $forget = $invoice->accurate()->forget();

        if ($invoice2 = $invoice->service_invoice) {
            $invoice2->accurate()->forget();
            $invoice2->delete();
        }

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
