<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\DeliveryOrder as Filters;
use App\Http\Requests\Income\DeliveryOrder as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\DeliveryOrder;
use App\Models\Income\RequestOrder;
use App\Models\Income\RequestOrderItem;
use App\Traits\GenerateNumber;

class DeliveryOrders extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $delivery_orders = DeliveryOrder::filter($filters)->get();
                break;

            case 'datagrid':
                $delivery_orders = DeliveryOrder::with(['customer','operator','vehicle'])->filter($filters)->orderBy('id', 'DESC')->latest()->get();
                $delivery_orders->each->setAppends(['is_relationship']);
                break;

            default:
                $delivery_orders = DeliveryOrder::with(['customer','operator','vehicle'])->filter($filters)->orderBy('id', 'DESC')->latest()->collect();
                $delivery_orders->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($delivery_orders);
    }

    public function show($id)
    {

        $delivery_order = DeliveryOrder::with([
            'customer',
            'request_order',
            'delivery_order_items.item.item_units',
            'delivery_order_items.unit',
        ])->withTrashed()->findOrFail($id);

        $delivery_order->setAppends(['has_revision', 'has_relationship']);

        return response()->json($delivery_order);
    }

    public function update(Request $request, $id)
    {
        if (request('mode') == 'confirmation') {
            return $this->confirmation($id);
        }
        else if (request('mode') == 'revision') {
            return $this->revision($request, $id);
        }
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($delivery_order->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode!");
        if($mode == "DELETED" && $delivery_order->status != "OPEN") $this->error("The data $delivery_order->status state, is not allowed to be $mode!");

        $delivery_order->status = $mode;
        $delivery_order->save();

        foreach ($delivery_order->delivery_order_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        $delivery_order->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function confirmation($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        if ($delivery_order->status != "OPEN") $this->error("SJDO[$delivery_order->number] has not OPEN state. Confirmation not allowed!");

        foreach ($delivery_order->delivery_order_items as $detail) {
            if (!$detail->request_order_item) $this->error("SJDO DETAIL[#$detail->id] '". $detail->item->part_name ."' not has SO Relation. Confirmation not allowed!");
            $detail->request_order_item->calculate();
        }

        $delivery_order->status = 'CONFIRMED';
        $delivery_order->save();

        $this->setRequestOrderClosed($delivery_order->request_order);

        $this->DATABASE::commit();
        return $this->show($delivery_order->id);
    }

    private function setRequestOrderClosed($request_order)
    {
        $unconfirm = $request_order->delivery_orders->filter(function($delivery) {
            return $delivery->status != "CONFIRMED";
        });

        $delivered = round($request_order->total_unit_amount) == round($request_order->total_unit_delivery);

        if ($request_order->order_mode == "NONE" && $unconfirm->count() && $delivered) {
            $request_order->status = 'CLOSED';
            $request_order->save();
        }
    }

    public function revision($request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = DeliveryOrder::findOrFail($id);
        $request_order = $revise->request_order;

        if($revise) {
            if($revise->trashed()) $this->error("SJDO [". $revise->number ."] is trashed. REVISION Not alowed!");

            $revise->delivery_order_items->each(function($detail) use($revise){
                if($detail->trashed()) $this->error("Part [". $detail->item->part_name ."] is trashed. REVISION Not alowed!");

                if($revise->request_order->order_mode == 'ACCUMULATE') {
                    $request_order_item = $detail->request_order_item;
                    $request_order_item->item->distransfer($request_order_item);
                    $request_order_item->forceDelete();
                }
                else {
                    $detail->request_order_item()->associate(null);
                }
                $detail->item->distransfer($detail);
                $detail->save();
                $detail->delete();
            });
        }

        // Auto generate number of revision
        if($request->number) {
            $max = (int) DeliveryOrder::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number'=> ($max + 1)]);
        }

        $delivery_order = DeliveryOrder::create($request->all());

        $rows = $request->delivery_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // IF "ACCUMULATE" create RequestOrder items on the Delivery order revision!
            if($request_order->order_mode == 'ACCUMULATE') {
                $request_order_item = $request_order->request_order_items()->create(array_merge($row, ['price' => 0]));
            }
            else {
                $request_order_item = RequestOrderItem::find($row['request_order_item_id']);
            }

            // create DeliveryOrder items on the Delivery order revision!
            $detail = $delivery_order->delivery_order_items()->create($row);
            $detail->item->transfer($detail, $detail->unit_amount, null, 'FG');

            $TransDO = $delivery_order->transaction == "RETURN" ? 'PDO.RET' : 'PDO.REG';
            $detail->item->transfer($detail, $detail->unit_amount, null, $TransDO);
            $detail->item->transfer($detail, $detail->unit_amount, null, 'VDO');

            $detail->request_order_item()->associate($request_order_item);
            $detail->save();

            if($detail->request_order_item) {
                if(round($detail->request_order_item->amount_delivery) > round($detail->request_order_item->unit_amount)) {
                    $max = round($detail->request_order_item->unit_amount - $detail->request_order_item->amount_delivery);
                    $this->error("Part [". $detail->item->part_name ."] unit maximum '$max'");
                }
            }
            else $this->error("Part [". $detail->item->part_name ."] relation [#$detail->request_order_item] undifined!");
        }
        $delivery_order->status = 'CONFIRMED';
        $delivery_order->request_order_id = $request->request_order_id;
        $delivery_order->outgoing_good_id = $request->outgoing_good_id;
        $delivery_order->save();

        $revise->revise_id = $delivery_order->id;
        $revise->status = 'REVISED';
        $revise->save();
        $revise->delete();

        // $this->error($request_order->request_order_items);

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }
}
