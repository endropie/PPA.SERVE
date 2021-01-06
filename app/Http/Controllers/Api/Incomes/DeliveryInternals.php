<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Filters\Filter as Filter;
use App\Http\Requests\Request;
use App\Http\Controllers\ApiController;
use App\Models\Income\DeliveryInternal;
use App\Traits\GenerateNumber;

class DeliveryInternals extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filters)
    {
        switch (request('mode')) {
            case 'all':
                $delivery_internals = DeliveryInternal::filter($filters)->get();
                break;

            case 'datagrid':
                $delivery_internals = DeliveryInternal::filter($filters)->latest()->get();

                break;

            default:
                $delivery_internals = DeliveryInternal::with('customer','created_user')->filter($filters)->latest()->collect();
                break;
        }

        return response()->json($delivery_internals);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextDeliveryInternalNumber()]);

        $request->validate([
            "number" => "required",
            "customer_id" => "required",
            "date" => "required",
            "delivery_internal_items" => "required|array",
            "delivery_internal_items.*.item_id" => "nullable|exists:items,id",
            "delivery_internal_items.*.name" => "required",
            "delivery_internal_items.*.quantity" => "required",
            "delivery_internal_items.*.unit_id" => "required",
        ]);

        $delivery_internal = DeliveryInternal::create($request->all());

        foreach ($request->delivery_internal_items as $row) {
            $delivery_internal->delivery_internal_items()->create($row);
        }

        // $this->error('LOLOS');

        $this->DATABASE::commit();

        return response()->json($delivery_internal);
    }

    public function show($id)
    {
        $delivery_internal = DeliveryInternal::with('customer','delivery_internal_items.item','delivery_internal_items.unit','created_user')->findOrFail($id);

        return response()->json($delivery_internal);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "number" => "required",
            "customer_id" => "required",
            "date" => "required",
            "delivery_internal_items" => "required|array",
            "delivery_internal_items.*.name" => "required",
            "delivery_internal_items.*.subname" => "required",
            "delivery_internal_items.*.quantity" => "required",
            "delivery_internal_items.*.item_id" => "nullable|exists:items,id",
            "delivery_internal_items.*.unit_id" => "required",
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_internal = DeliveryInternal::findOrFail($id);

        if ($delivery_internal->status !== 'OPEN') $this->error('DELIVERY (INTERN) has not OPEN state, is not allowed to be changed!');

        $delivery_internal->update($request->input());

        $hasRowIDs = collect($request->delivery_internal_items)->whereNotNull('id')->pluck('id')->toArray();
        $delivery_internal->delivery_internal_items()->whereNotIn('id', $hasRowIDs)->forceDelete();

        foreach ($request->delivery_internal_items as $row)
        {
            $delivery_internal->delivery_internal_items()->updateOrCreate(['id' => $row['id'] ?? null], $row);
        }

        $this->DATABASE::commit();

        return response()->json($delivery_internal);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_internal = DeliveryInternal::findOrFail($id);

        if (request('mode') == "VOID")
        {
            $delivery_internal->status = "VOID";
            $delivery_internal->save();
        }

        $delivery_internal->delete();

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }

    public function confirmed($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_internal = DeliveryInternal::findOrFail($id);

        if ($delivery_internal->status !== 'OPEN') $this->error('DELIVERY (INTERN) has not OPEN state, is not allowed to be confirmed!');

        $delivery_internal->status = "CONFIRMED";
        $delivery_internal->save();

        $this->DATABASE::commit();

        return response()->json($delivery_internal);
    }

    public function revised(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_internal = DeliveryInternal::findOrFail($id);

        if ($delivery_internal->trashed()) $this->error('DELIVERY (INTERN) is trashed, is not allowed to be revised!');
        if ($delivery_internal->revised_number) $this->error('DELIVERY (INTERN) has revised, is not allowed to be revised!');

        $request->validate(['revised_number' => 'required']);

        $delivery_internal->revised_number = $request->revised_number;

        $delivery_internal->save();

        $this->DATABASE::commit();

        return response()->json($delivery_internal);
    }
}
