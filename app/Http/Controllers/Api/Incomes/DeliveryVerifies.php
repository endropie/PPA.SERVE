<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request;
// use App\Http\Requests\Income\DeliveryVerifyItem as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter as Filter;
use App\Models\Income\DeliveryVerify;
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
        if (request('mode') == 'multi-store') return $this->multiStore($request);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            // 'rit' => 'required',
            'item_id' => 'required|exists:items,id',
            'unit_id' => 'required',
            'unit_rate' => 'required',
            'quantity' => 'required',
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_verify = DeliveryVerify::create([
            'customer_id' => $request->get('customer_id'),
            'description' => $request->get('description'),
        ]);

        $delivery_verify_item = DeliveryVerifyItem::create($request->input());

        $delivery_verify_item->delivery_verify()->associate($delivery_verify);
        $delivery_verify_item->save();

        $label = $delivery_verify_item->item->part_name . "(". $delivery_verify_item->item->code .")";

        $request->validate(
            ["quantity" => "numeric|gt:0|lte:". $delivery_verify_item->maxNewVerifyAmount() ],
            ["quantity.lte" => "Maximum ". $delivery_verify_item->maxNewVerifyAmount() .". Part: ". $label]
        );

        $delivery_verify_item->setCommentLog("VERIFY #$delivery_verify_item->id [$label] has been created!");

        $this->DATABASE::commit();
        return response()->json($delivery_verify);
    }

    public function multiStore(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            // 'rit' => 'required',
            'multi_items' => "required|array|min:1",
            'multi_items.*.item_id' => 'required|exists:items,id',
            'multi_items.*.unit_id' => 'required',
            'multi_items.*.unit_rate' => 'required',
            'multi_items.*.quantity' => 'required',
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_verify = DeliveryVerify::create([
            'customer_id' => $request->get('customer_id'),
            'description' => $request->get('description'),
        ]);

        foreach ($request->multi_items as $key => $row) {
            $input = array_merge($row, [
                "customer_id" => $request->customer_id,
                "date" => $request->date,
                // "rit" => $request->rit
            ]);

            $delivery_verify_item = DeliveryVerifyItem::create($input);

            $delivery_verify_item->delivery_verify()->associate($delivery_verify);
            $delivery_verify_item->save();

            $label = $delivery_verify_item->item->part_name . "(". $delivery_verify_item->item->code .")";

            $request->validate(
                ["multi_items.$key.quantity" => "numeric|gt:0|lte:". $delivery_verify_item->maxNewVerifyAmount() ],
                ["multi_items.$key.quantity.lte" => "Maximum ". $delivery_verify_item->maxNewVerifyAmount() .". Part: ". $label ]
            );

            $delivery_verify_item->setCommentLog("VERIFY #$delivery_verify_item->id [$label] has been created!");
        }

        $this->DATABASE::commit();
        return response()->json($delivery_verify);

    }

    public function show($id)
    {
        $delivery_verify = DeliveryVerify::with([
            'customer',
            'delivery_verify_items',
            'delivery_verify_items.item.item_units',
            'delivery_verify_items.item.unit',
            'delivery_verify_items.unit',
        ])->withTrashed()->findOrFail($id);

        return response()->json($delivery_verify);
    }

    public function detail($id)
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
            // 'rit' => 'required',
            'item_id' => 'required|exist:items,id',
            'unit_id' => 'required',
            'unit_rate' => 'required',
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_verify_item = DeliveryVerifyItem::findOrFail($id);

        $delivery_verify_item->update($request->input());

        $delivery_verify_item->setCommentLog("VERIFY #$delivery_verify_item->id has been updated!");

        $this->DATABASE::commit();
        return response()->json($delivery_verify_item);
    }

    public function destroy($id)
    {

        $this->DATABASE::beginTransaction();

        $delivery_verify = DeliveryVerify::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        foreach ($delivery_verify->delivery_verify_items as $delivery_verify_item) {

            if ($delivery_verify_item->validateDestroyVerified() != true)
            {
                $this->error("The `". $delivery_verify_item->item->part_name ."` not allowed to be $mode");
            }

            $delivery_verify_item->delete();

            $delivery_verify_item->setCommentLog("VERIFY #$delivery_verify_item->id has been $mode !");
        }

        $delivery_verify->delete();

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }

    public function destroyDetail($id)
    {

        $this->DATABASE::beginTransaction();

        $delivery_verify_item = DeliveryVerifyItem::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        if ($delivery_verify_item->validateDestroyVerified() != true)
        {
            $this->error("The `". $delivery_verify_item->item->part_name ."` not allowed to be $mode");
        }

        $delivery_verify_item->delete();

        $delivery_verify_item->setCommentLog("VERIFY #$delivery_verify_item->id has been $mode !");

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }
}
