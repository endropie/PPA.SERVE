<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\DeliveryOrder as Filter;
use App\Filters\Income\DeliveryOrderItem as FilterItem;
use App\Http\Requests\Income\DeliveryOrder as Request;
use App\Http\Requests\Request as BaseRequest;
use App\Http\Controllers\ApiController;
use App\Models\Income\Customer;
use App\Models\Income\DeliveryOrder;
use App\Models\Income\DeliveryOrderItem;
use App\Models\Income\RequestOrder;
use App\Models\Income\RequestOrderItem;
use App\Traits\GenerateNumber;

class DeliveryOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $delivery_orders = DeliveryOrder::filter($filter)->get();
                break;

            case 'datagrid':
                $delivery_orders = DeliveryOrder::with(['customer', 'operator', 'vehicle'])->filter($filter)->orderBy('id', 'DESC')->latest()->get();
                $delivery_orders->each->append(['is_relationship']);
                break;

            default:
                $delivery_orders = DeliveryOrder::with(['created_user', 'customer', 'operator', 'vehicle'])->filter($filter)->orderBy('id', 'DESC')->latest()->collect();
                $delivery_orders->getCollection()->transform(function ($item) {
                    $item->append(['is_relationship', 'summary_items']);
                    return $item;
                });
                break;
        }

        return response()->json($delivery_orders);
    }

    public function items(FilterItem $filter_item)
    {
        switch (request('mode')) {
            case 'all':
                $delivery_order_items = DeliveryOrderItem::filter($filter_item)->latest()->get();
                break;

            default:
                $delivery_order_items = DeliveryOrderItem::with(['unit', 'item', 'delivery_order'])
                    ->filter($filter_item)
                    ->latest()->collect();

                $delivery_order_items->getCollection()->transform(function ($row) {
                    return $row;
                });

                break;
        }

        return response()->json($delivery_order_items);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        ## Form validate is SAMPLE transaction only
        $request->validate(['transaction' => 'in:SAMPLE']);

        if ($customer = Customer::find($request->get('customer_id'))) {
            $prefix_code = $customer->code ?? "C:$customer->id";
        } else $request->validate(['customer_id' => 'not_in:' . $request->get('customer_id')]);

        if (!$request->number) $request->merge([
            'number' => $this->getNextSJDeliveryNumber($request->get('date')),
            'indexed_number' => $this->getNextSJDeliveryIndexedNumber($request->get('date'), $prefix_code),
        ]);

        $delivery_order = DeliveryOrder::create($request->input());

        foreach ($request->delivery_order_items as $row) {
            ## create DeliveryOrder items on the Delivery order revision!
            $detail = $delivery_order->delivery_order_items()->create($row);
        }

        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been created!");

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }

    public function storeInternal(Request $request)
    {
        $this->DATABASE::beginTransaction();

        ## Form validate is SAMPLE transaction only
        $request->validate(['transaction' => 'not_in:SAMPLE']);

        if ($customer = Customer::find($request->get('customer_id'))) {
            $prefix_code = $customer->code ?? "C:$customer->id";
        } else $request->validate(['customer_id' => 'not_in:' . $request->get('customer_id')]);

        if (!$request->number) $request->merge([
            'number' => $this->getNextSJInternalNumber($request->get('date')),
            'indexed_number' => $this->getNextSJDeliveryIndexedNumber($request->get('date'), $prefix_code),
        ]);

        $delivery_order = DeliveryOrder::create($request->input());

        $validator = array();
        $validtext = array();

        foreach ($request->delivery_order_items as $i => $row) {
            ## create DeliveryOrder items on the Delivery order revision!
            $detail = $delivery_order->delivery_order_items()->create($row);

            $transfer = $detail->item->handleStockLessTransfer()->transfer($detail, $detail->unit_amount, null, "FG");
            if ($transfer == false) {
                $max = round($detail->item->getTotalStockist('FG') / ($detail->unit_rate || 1));
                $validator["delivery_order_items.$i.quantity"] = "required|numeric|gt:0|lte:" . round($max);
                $validtext["delivery_order_items.$i.quantity.lte"] = "Maximum [FG: $max]";
            }
        }

        $request->validate($validator, $validtext);

        $request->validate([
            'internal_reason_id' => 'required',
            'internal_reason_description' => 'required_if:internal_reason_id,null',
        ]);

        $delivery_order->internal_reason_id = $request->internal_reason_id;
        $delivery_order->internal_reason_description = $request->internal_reason_description;
        $delivery_order->save();

        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been created!");

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }

    public function show($id)
    {
        $delivery_order = DeliveryOrder::with([
            'created_user',
            'customer',
            'vehicle',
            'request_order',
            'revisions',
            'reason',
            'delivery_load',
            'delivery_order_items.item.unit',
            'delivery_order_items.unit',
            'delivery_order_items.item.item_units',
            'delivery_order_items.request_order_item',
        ])->withTrashed()->findOrFail($id);

        $delivery_order->append(['has_relationship']);

        return response()->json($delivery_order);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        ## Form validate is SAMPLE transaction only
        $request->validate(['transaction' => 'in:SAMPLE']);

        $delivery_order = DeliveryOrder::findOrFail($id);
        $delivery_order->update($request->input());

        ## DISCARD Delivery order items REMOVED!
        $collection = collect($request->delivery_order_items);
        foreach ($delivery_order->delivery_order_items as $detail) {
            if (!$collection->contains('id', $detail->id)) {
                $detail->forceDelete();
            }
        }

        foreach ($request->delivery_order_items as $row) {
            ## create DeliveryOrder items on the Delivery order revision!
            $detail = $delivery_order->delivery_order_items()->updateOrCreate(['id' => $row['id']], $row);
        }

        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been updated!");

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if ($delivery_order->acc_invoice_id) $this->error("The data has Invoice Collect, is not allowed to be $mode!");
        if ($delivery_order->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode!");
        if ($mode == "DELETED" && $delivery_order->status != "OPEN") $this->error("The data $delivery_order->status state, is not allowed to be $mode!");

        foreach ($delivery_order->delivery_order_items as $detail) {
            $request_order_item = $detail->request_order_item;

            $detail->item->distransfer($detail);

            $detail->request_order_item()->dissociate();
            $detail->save();


            if ($request_order_item) {
                $request_order_item->calculate();
                if ($request_order_item->request_order->order_mode == 'ACCUMULATE') {
                    $request_order_item->forceDelete();
                }
            }

            $detail->delete();
        }

        $delivery_order->status = $mode;
        $delivery_order->request_order()->dissociate();
        $delivery_order->save();

        $delivery_order->delete();

        $action = ($mode == "VOID") ? 'voided' : 'deleted';
        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been $action !");

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function encasementItem(BaseRequest $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        if ($delivery_order->status != "OPEN") $this->error("SJDO[$delivery_order->number] has not OPEN state. Update not allowed!");

        $request->validate(['id' => 'required']);

        $delivery_order_item = DeliveryOrderItem::findOrFail($request->id);

        $delivery_order_item->update($request->input());

        $this->DATABASE::commit();
        return $this->show($delivery_order->id);
    }

    public function reconfirmation($id)
    {
        $delivery_order = DeliveryOrder::findOrFail($id);

        if ($delivery_order->status != "VALIDATED") $this->error("SJDO[$delivery_order->number] has not VALIDATED state. Reconfirmation not allowed!");

        if ($delivery_order->acc_invoice) $this->error("SJDO[$delivery_order->number] has invoice collect. Reconfirmation not allowed!");

        $this->DATABASE::beginTransaction();

        foreach ($delivery_order->delivery_order_items as $detail) {
            if ($detail->request_order_item) $detail->request_order_item->calculate();
        }

        if (request('confirmed_number')) {
            $delivery_order->confirmed_number = request('confirmed_number');
        }

        $delivery_order->status = 'CONFIRMED';
        $delivery_order->confirmed_by = auth()->user()->id;
        $delivery_order->confirmed_at = now();
        $delivery_order->save();

        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been confirmed!");

        $this->DATABASE::commit();
        return $this->show($delivery_order->id);
    }

    public function confirmation($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        if ($delivery_order->status != "OPEN") $this->error("SJDO[$delivery_order->number] has not OPEN state. Confirmation not allowed!");

        foreach ($delivery_order->delivery_order_items as $detail) {
            if ($detail->request_order_item) $detail->request_order_item->calculate();
        }

        if (request('confirmed_number')) {
            $delivery_order->confirmed_number = request('confirmed_number');
        }

        $delivery_order->status = 'CONFIRMED';
        $delivery_order->confirmed_by = auth()->user()->id;
        $delivery_order->confirmed_at = now();
        $delivery_order->save();

        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been confirmed!");

        $this->DATABASE::commit();
        return $this->show($delivery_order->id);
    }

    public function validation($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        if ($delivery_order->status != "CONFIRMED") $this->error("SJDO[$delivery_order->number] has '$delivery_order->status' state. Validation not allowed!");

        foreach ($delivery_order->delivery_order_items as $detail) {
            if ($detail->request_order_item) $detail->request_order_item->calculate();
        }

        $delivery_order->status = 'VALIDATED';
        $delivery_order->validated_by = auth()->user()->id;
        $delivery_order->validated_at = now();
        $delivery_order->save();

        if ($delivery_order->request_order) $this->setRequestOrderClosed($delivery_order->request_order);

        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been validated!");

        $this->DATABASE::commit();
        return $this->show($delivery_order->id);
    }

    public function multiValidation(BaseRequest $request)
    {
        $this->DATABASE::beginTransaction();

        foreach ($request->get('data') as $key => $row) {

            $delivery_order = DeliveryOrder::findOrFail($row['id']);

            if ($delivery_order->status != "CONFIRMED") $this->error("SJDO[$delivery_order->number] has '$delivery_order->status' state. Validation not allowed!");

            foreach ($delivery_order->delivery_order_items as $detail) {
                if ($detail->request_order_item) $detail->request_order_item->calculate();
            }

            $delivery_order->status = 'VALIDATED';
            $delivery_order->validated_by = auth()->user()->id;
            $delivery_order->validated_at = now();
            $delivery_order->save();

            if ($delivery_order->request_order) $this->setRequestOrderClosed($delivery_order->request_order);
        }

        $delivery_order->setCommentLog("SJ Delivery has been validated!");

        $this->DATABASE::commit();
        return $this->show($delivery_order->id);
    }

    public function reopen($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        if ($delivery_order->status != "CONFIRMED") $this->error("SJDO[$delivery_order->number] has not '$delivery_order->status' state. Reopen not allowed!");

        foreach ($delivery_order->delivery_order_items as $detail) {
            if ($detail->request_order_item) $detail->request_order_item->calculate();
        }

        $delivery_order->status = 'OPEN';
        $delivery_order->confirmed_by = null;
        $delivery_order->confirmed_at = null;
        $delivery_order->save();

        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been re-opened!");

        $this->DATABASE::commit();
        return $this->show($delivery_order->id);
    }

    public function multiRevision(BaseRequest $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = DeliveryOrder::findOrFail($id);
        $request_order = $revise->request_order;

        if ($revise->acc_invoice_id) $this->error("The data has Invoice Collect, MANY-REVISION Not alowed !");

        if ($revise->trashed()) $this->error("[" . $revise->number . "] is trashed. MANY-REVISION Not alowed!");
        if ($revise->is_internal) $this->error("[" . $revise->number . "] is INTERNAL. MANY-REVISION Not alowed!");
        if (!$request_order) $this->error("[" . $revise->number . "] Order Failed. MANY-REVISION Not alowed!");
        if ($revise->status != 'OPEN') {
            if ($revise->request_order->status == 'CLOSED') $this->error("[" . $revise->request_order->number . "] has CLOSED. REVISION Not alowed!");
        }

        ## Remove detail of revision
        foreach ($revise->delivery_order_items as $detail) {
            if ($request_order_item = $detail->request_order_item) {
                if ($revise->request_order->order_mode == 'ACCUMULATE') {
                    $request_order_item->item->distransfer($request_order_item);
                    $request_order_item->forceDelete();
                }
            }

            $detail->item->distransfer($detail);
            $detail->delete();

            if ($request_order_item = $detail->request_order_item) {
                $request_order_item->calculate();
            }

            $detail->request_order_item()->dissociate();
            $detail->save();
        }

        $request->validate([
            'partitions' => 'required',
            'partitions.*.request_order_id' => 'required',
            'partitions.*.transaction' => 'required',
        ]);

        ## New delivery order of partitions
        $validator = array();
        $validtext = array();
        foreach ($request->partitions as $key => $partition) {
            ## Auto generate number of revision
            $max = (int) DeliveryOrder::withTrashed()->where('number', $request->number)->max('revise_number');

            $request->merge([
                'revise_number' => ($max + 1),
                'transaction' => $partition['transaction'],
                'description' => $partition['description'],
            ]);

            $delivery_order = DeliveryOrder::create($request->all());
            $request_order = RequestOrder::find($partition['request_order_id']);

            if (!$request_order) {
                $validator["partitions.$key.request_order_id"] = "not_in:" . $partition["request_order_id"];
                continue;
            }

            $rows = $partition['delivery_order_items'];
            foreach ($rows as $i => $row) {

                ## IF "ACCUMULATE" create RequestOrder items on the Delivery order revision!
                if ($request_order->order_mode == 'ACCUMULATE') {
                    $request_order_item = $request_order->request_order_items()->create(array_merge($row, ['price' => 0]));
                    ## Setup unit price
                    $request_order_item->price = ($request_order_item->item && $request_order_item->item->price)
                        ? (float) $request_order_item->unit_rate * (float) $request_order_item->item->price : 0;
                    $request_order_item->save();
                } else {
                    $request_order_item = RequestOrderItem::find($row['request_order_item_id']);
                }

                ## create DeliveryOrder items on the Delivery order revision!
                $detail = $delivery_order->delivery_order_items()->create($row);
                $transfer = $detail->item->handleStockLessTransfer()->transfer($detail, $detail->unit_amount, null, 'FG');
                if ($transfer == false) {
                    $max = round($detail->item->getTotalStockist('FG') / ($detail->unit_rate || 1));
                    $validator["partitions.$key.delivery_order_items.$i.quantity"] = "required|numeric|gt:0|lte:" . round($max);
                    $validtext["partitions.$key.delivery_order_items.$i.quantity.lte"] = "Maximum [FG: $max]";
                }

                $detail->request_order_item()->associate($request_order_item);
                $detail->save();

                if (!$detail->request_order_item) {
                    $validator["partitions.$key.delivery_order_items.$i.request_order_item_id"] = "required|not_in:". $row["request_order_item_id"];
                    $validtext["partitions.$key.delivery_order_items.$i.quantity.not_in"] = "The order item invalid";
                }
                else {
                    $max = round($detail->request_order_item->unit_amount - $detail->request_order_item->amount_delivery);
                    if (round($detail->unit_amount) > $max) {
                        $max = round($max / ($detail->unit_rate || 1));
                        $validator["partitions.$key.delivery_order_items.$i.quantity"] = "required|numeric|gt:0|lte:$max";
                        $validtext["partitions.$key.delivery_order_items.$i.quantity.lte"] = "Maximum [order item #$detail->request_order_item_id: $max]";
                    }

                    $request_order_item->calculate();
                }
            }

            $delivery_order->request_order_id = $partition["request_order_id"];
            $delivery_order->revise_id = $revise->id;
            $delivery_order->save();

            $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been created!\nOn revision $revise->fullnumber.");
        }

        $request->validate($validator, $validtext);

        $revise->request_order()->dissociate();
        $revise->status = 'REVISED';
        $revise->reason_id = $request->get('reason_id', null);
        $revise->reason_description = $request->get('reason_description', null);
        $revise->save();
        $revise->delete();

        $revise->setCommentLog("SJ Delivery [$revise->fullnumber] has been revised!");

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }

    public function revision(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = DeliveryOrder::findOrFail($id);
        $request_order = $revise->request_order;

        if ($revise->acc_invoice_id) $this->error("The data has Invoice Collect, REVISION Not alowed!");

        if ($revise->trashed()) $this->error("[" . $revise->number . "] is trashed. REVISION Not alowed!");

        if ($revise->is_internal) $this->error("[" . $revise->number . "] not OPEN. REVISION Not alowed!");

        if ($request_order = $revise->request_order) {
            if ($request_order->status == 'CLOSED') $this->error("[" . $revise->request_order->number . "] has CLOSED. REVISION Not alowed!");
            if ($request_order->acc_invoice_id) $this->error("[" . $revise->request_order->number . "] has INVOICED. REVISION Not alowed!");
        }

        ## Remove detail of revision
        foreach ($revise->delivery_order_items as $detail) {
            if ($revise->request_order->order_mode == 'ACCUMULATE') {
                $request_order_item = $detail->request_order_item;
                $request_order_item->item->distransfer($request_order_item);
                $request_order_item->forceDelete();
            }

            $detail->item->distransfer($detail);
            $detail->delete();

            if ($request_order_item = $detail->request_order_item) {
                $request_order_item->calculate();
            }

            $detail->request_order_item()->dissociate();
            $detail->save();
        }

        ## Auto generate number of revision
        if ($request->number) {
            $max = (int) DeliveryOrder::withTrashed()
                ->selectRaw('MAX(revise_number * 1) AS N')
                ->where('number', $request->number)
                ->get()->max('N');

            $request->merge(['revise_number' => ($max + 1)]);
        }

        $delivery_order = DeliveryOrder::create($request->all());

        $request_order = RequestOrder::find($request['request_order_id']);
        if (!$request_order) $request->validate(["request_order_id" => "not_in:" . $request["request_order_id"]]);

        $validator = array();
        $validtext = array();
        $rows = $request->delivery_order_items;
        foreach ($rows as $i => $row) {

            if ($request_order->order_mode == 'ACCUMULATE') {
                $request_order_item = $request_order->request_order_items()->create(array_merge($row, ['price' => 0]));
                ## Setup unit price
                $request_order_item->price = ($request_order_item->item && $request_order_item->item->price)
                    ? (float) $request_order_item->unit_rate * (float) $request_order_item->item->price : 0;
                $request_order_item->save();
            } else {
                $request_order_item = RequestOrderItem::find($row['request_order_item_id']);
            }

            ## create DeliveryOrder items on the Delivery order revision!
            $detail = $delivery_order->delivery_order_items()->create($row);

            if ($delivery_order->revise_nc) {
                if (($dividen = ($detail->quantity_nc) * $detail->unit_rate) > 0) {
                    $detail->item->handleStockLessTransfer()->transfer($detail, $dividen, 'NCR', 'FG');
                }
            }

            $transfer = $detail->item->handleStockLessTransfer()->transfer($detail, $detail->unit_amount, null, 'FG');


            if ($transfer == false) {
                $max = round($detail->item->getTotalStockist('FG') / ($detail->unit_rate || 1));
                $validator["delivery_order_items.$i.quantity"] = "required|numeric|gt:0|lte:" . round($max);
                $validtext["delivery_order_items.$i.quantity.lte"] = "Maximum [FG: $max]";
            }

            $detail->request_order_item()->associate($request_order_item);
            $detail->save();

            if (!$detail->request_order_item) {
                $validator["delivery_order_items.$i.request_order_item_id"] = "required|not_in:". $row["request_order_item_id"];
                $validtext["delivery_order_items.$i.quantity.not_in"] = "The order item invalid";
            }
            else {
                $max = round($detail->request_order_item->unit_amount - $detail->request_order_item->amount_delivery);
                if (round($detail->unit_amount) > $max) {
                    $max = round($max / ($detail->unit_rate || 1));
                    $validator["delivery_order_items.$i.quantity"] = "required|numeric|gt:0|lte:" . round($max);
                    $validtext["delivery_order_items.$i.quantity.lte"] = "Maximum [order item #$detail->request_order_item_id]: $max";
                }

                $detail->request_order_item->calculate();
            }

        }

        $request->validate($validator, $validtext);

        $delivery_order->request_order_id = $request->request_order_id;
        $delivery_order->revise_id = $revise->id;
        $delivery_order->save();

        $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been created!\nOn revision [$revise->fullnumber]");

        $revise->request_order()->dissociate();
        $revise->status = 'REVISED';
        $revise->reason_id = $request->get('reason_id', null);
        $revise->reason_description = $request->get('reason_description', null);
        $revise->save();
        $revise->delete();

        $revise->setCommentLog("SJ Delivery [$revise->fullnumber] has been revised!");

        $this->DATABASE::commit();

        return response()->json($delivery_order);
    }

    public function revisonInternal(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = DeliveryOrder::findOrFail($id);

        if ($revise->acc_invoice_id) $this->error("The data has Invoice Collect, REVISION Not alowed !");
        if ($revise->trashed()) $this->error("[" . $revise->number . "] is trashed. REVISION Not alowed!");
        if (!$revise->is_internal) $this->error("[" . $revise->number . "] is not INTERNAL. REVISION Not alowed!");
        if ($revise->status == 'CLOSED') $this->error("[" . $revise->request_order->number . "] has CLOSED. REVISION Not alowed!");

        ## Remove detail of revision
        foreach ($revise->delivery_order_items as $detail) {

            $detail->item->distransfer($detail);
            $detail->delete();
            $detail->save();
        }

        $request->validate([
            'partitions' => 'required',
            'partitions.*.request_order_id' => 'required',
            'partitions.*.transaction' => 'required',
        ]);

        $validator = array();
        $validtext = array();

        ## New delivery order of partitions
        foreach ($request->partitions as $key => $partition) {
            ## Auto generate number of revision
            $number = $this->getNextSJDeliveryNumber($revise->date);

            $request->merge([
                'number' => $number,
                'revise_number' => 0,
                'transaction' => $partition['transaction'],
                'description' => $partition['description'],
            ]);

            $delivery_order = DeliveryOrder::create($request->all());
            $request_order = RequestOrder::find($partition['request_order_id']);

            if (!$request_order) {
                $validator["partitions.$key.request_order_id"] = "not_in:" . $partition["request_order_id"];
                continue;
            }

            $rows = $partition['delivery_order_items'];
            foreach ($rows as $i => $row) {

                ## IF "ACCUMULATE" create RequestOrder items on the Delivery order revision!
                if ($request_order->order_mode == 'ACCUMULATE') {
                    $request_order_item = $request_order->request_order_items()->create(array_merge($row, ['price' => 0]));
                    ## Setup unit price
                    $request_order_item->price = ($request_order_item->item && $request_order_item->item->price)
                        ? (float) $request_order_item->unit_rate * (float) $request_order_item->item->price : 0;
                    $request_order_item->save();
                } else {
                    $request_order_item = RequestOrderItem::find($row['request_order_item_id']);
                }

                ## create DeliveryOrder items on the Delivery order revision!
                $detail = $delivery_order->delivery_order_items()->create($row);
                $transfer = $detail->item->handleStockLessTransfer()->transfer($detail, $detail->unit_amount, null, 'FG');
                if ($transfer == false) {
                    $max = round($detail->item->getTotalStockist('FG') / ($detail->unit_rate || 1));
                    $validator["partitions.$key.delivery_order_items.$i.quantity"] = "required|numeric|gt:0|lte:" . round($max);
                    $validtext["partitions.$key.delivery_order_items.$i.quantity.lte"] = "Maximum [FG: $max]";
                }

                $detail->request_order_item()->associate($request_order_item);
                $detail->save();

                if (!$detail->request_order_item) {
                    $validator["partitions.$key.delivery_order_items.$i.request_order_item_id"] = "required|not_in:". $row["request_order_item_id"];
                    $validtext["partitions.$key.delivery_order_items.$i.quantity.not_in"] = "The order item invalid";
                }
                else {
                    $max = round($detail->request_order_item->unit_amount - $detail->request_order_item->amount_delivery);
                    if (round($detail->unit_amount) > $max) {
                        $max = round($max / ($detail->unit_rate || 1));
                        $validator["partitions.$key.delivery_order_items.$i.quantity"] = "required|numeric|gt:0|lte:$max";
                        $validtext["partitions.$key.delivery_order_items.$i.quantity.lte"] = "Maximum [order item #$detail->request_order_item_id: $max]";
                    }

                    $request_order_item->calculate();
                }
            }

            $delivery_order->is_internal = 0;
            $delivery_order->request_order_id = $partition["request_order_id"];
            $delivery_order->revise_id = $revise->id;
            $delivery_order->save();

            $delivery_order->setCommentLog("SJ Delivery [$delivery_order->fullnumber] has been created!\nOn revision [$revise->fullnumber]");
        }

        $request->validate($validator, $validtext);

        $revise->status = 'REVISED';
        $revise->reason_id = $request->get('reason_id', null);
        $revise->reason_description = $request->get('reason_description', null);
        $revise->save();
        $revise->delete();

        $revise->setCommentLog("SJ Delivery [$revise->fullnumber] has been revised!");

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }

    private function setRequestOrderClosed($request_order)
    {
        $unconfirm = $request_order->delivery_orders->filter(function ($delivery) {
            return $delivery->status != "CONFIRMED";
        });

        $delivered = round($request_order->total_unit_amount) == round($request_order->total_unit_delivery);

        if ($request_order->order_mode == "NONE" && $unconfirm->count() == 0 && $delivered) {
            $request_order->status = 'CLOSED';
            $request_order->save();

            $request_order->setCommentLog("SO [$request_order->fullnumber] has been closed!");
        }
    }
}
