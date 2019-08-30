<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Income\DeliveryOrder as Filters;
use App\Http\Requests\Income\DeliveryOrder as Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\DeliveryOrder;
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
                $delivery_orders = DeliveryOrder::with(['customer','operator','vehicle'])->filter($filters)->latest()->get();
                $delivery_orders->each->setAppends(['is_relationship']);
                break;

            default:
                $delivery_orders = DeliveryOrder::with(['customer','operator','vehicle'])->filter($filters)->latest()->collect();
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

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_order = DeliveryOrder::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($delivery_order->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode!");
        if($mode == "DELETED" && $delivery_order->status != "OPEN") $this->error("The data $delivery_order->status state, is not allowed to be $mode!");

        if ($mode == "VOID") {
            $delivery_order->status = 'VOID';
            $delivery_order->save();
        }

        foreach ($delivery_order->delivery_order_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        $delivery_order->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function revision(Request $request, $id)
    {

        $this->DATABASE::beginTransaction();

        // $this->error('REVISION');
        $revise = DeliveryOrder::findOrFail($id);
        if($revise) {
            // $revise->delivery_order_items->each(function($detail, $i) {
            //     $detail->request_order_item_id = null;
            //     $detail->save();
            // });
        }

        // Auto generate number of revision
        if($request->number) {
            $max = (int) DeliveryOrder::where('number', $request->number)->max('numrev');
            $request->merge(['numrev'=> ($max + 1)]);
        }

        $delivery_order = DeliveryOrder::create($request->all());

        $rows = $request->delivery_order_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create DeliveryOrder items on the Delivery order revision!
            $detail = $delivery_order->delivery_order_items()->create($row);

            // $detail->request_order_item_id = $row['request_order_item_id'];
            // $detail->save();

            if($detail->request_order_item) {
                if($detail->request_order_item->total_delivery_order_item > ($detail->request_order_item->unit_amount + 0.1)) {
                    $this->error("Data is not allowed to be changed [".$detail->request_order_item->total_delivery_order_item .">". ($detail->request_order_item->unit_amount)."]");
                }
            }
        }

        $delivery_order->request_order_id = $request->request_order_id;
        $delivery_order->outgoing_good_id = $request->outgoing_good_id;
        $delivery_order->save();


        $revise->revise_id = $delivery_order->id;
        $revise->status = 'REVISE';
        $revise->save();

        $this->error('LOLOS');

        $this->DATABASE::commit();
        return response()->json($delivery_order);
    }
}
