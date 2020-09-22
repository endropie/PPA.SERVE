<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request;
// use App\Http\Requests\Income\DeliveryVerifyItem as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter as Filter;
// use App\Filters\Income\DeliveryVerifyItem as Filters;
use App\Models\Income\DeliveryVerifyItem;
use App\Traits\GenerateNumber;

class DeliveryVerifies extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $delivery_verify_items = DeliveryVerifyItem::filter($filter)->latest()->get();
                break;

            case 'datagrid':
                $delivery_verify_items = DeliveryVerifyItem::with(['customer'])->filter($filter)->latest()->get();
                break;

            default:
                $delivery_verify_items = DeliveryVerifyItem::with(['created_user','customer','item', 'unit'])->filter($filter)->latest()->collect();
                // $delivery_verify_items->getCollection()->transform(function($item) {
                //     return $item;
                // });
                break;
        }

        return response()->json($delivery_verify_items);
    }


    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'rit' => 'required',
            'item_id' => 'required|exists:items,id',
            'unit_id' => 'required',
            'unit_rate' => 'required',
            'quantity' => 'required',
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_verify_item = DeliveryVerifyItem::create($request->input());

        $request->validate(
            ["quantity" => "lte:". $delivery_verify_item->maxNewVerifyAmount() ],
            ["quantity" => "Maximum ". $delivery_verify_item->maxNewVerifyAmount() ]
        );

        $this->DATABASE::commit();
        return response()->json($delivery_verify_item);
    }

    public function show($id)
    {
        $delivery_verify_item = DeliveryVerifyItem::with([
            'customer',
            'item.item_units',
            'item.unit',
            'unit',
        ])->withTrashed()->findOrFail($id);

        $delivery_verify_item->append(['has_relationship']);

        return response()->json($delivery_verify_item);
    }

    public function update(Request $request, $id)
    {
        $this->error('NOT ALLOWED!');

        $request->validate([
            'customer_id' => 'required|exist:customers,id',
            'date' => 'required|date',
            'rit' => 'required',
            'item_id' => 'required|exist:items,id',
            'unit_id' => 'required',
            'unit_rate' => 'required',
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_verify_item = DeliveryVerifyItem::findOrFail($id);

        $delivery_verify_item->update($request->input());

        $this->DATABASE::commit();
        return response()->json($delivery_verify_item);
    }

    public function destroy($id)
    {

        $this->DATABASE::beginTransaction();

        $delivery_verify_item = DeliveryVerifyItem::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        if ($delivery_verify_item->validateDestroyVerified() != true) {
            $this->error("The `". $delivery_verify_item->item->part_name ."` not allowed to be $mode");
        }

        if($mode == "VOID") {
            $delivery_verify_item->status = "VOID";
            $delivery_verify_item->save();
        }

        $delivery_verify_item->delete();

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }
}
