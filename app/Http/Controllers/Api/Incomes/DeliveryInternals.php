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
                $delivery_internals = DeliveryInternal::with('customer','created_user')->filter($filters)->collect();
                break;
        }

        return response()->json($delivery_internals);
    }

    public function store(Request $request)
    {
        if(!$request->number) $request->merge(['number'=> $this->getNextDeliveryInternalNumber()]);

        $request->validate([
            "number" => "required",
            "customer_id" => "required",
            "date" => "required",
            "option" => "required",
            "option.delivery_internal_items.*.name" => "required",
            "option.delivery_internal_items.*.quantity" => "required",
        ]);

        $delivery_internal = DeliveryInternal::create($request->all());

        return response()->json($delivery_internal);
    }

    public function show($id)
    {
        $delivery_internal = DeliveryInternal::with('customer','created_user')->findOrFail($id);

        return response()->json($delivery_internal);
    }

    public function update(Request $request, $id)
    {
        $delivery_internal = DeliveryInternal::findOrFail($id);
        $delivery_internal->update($request->input());

        return response()->json($delivery_internal);
    }

    public function destroy($id)
    {
        $delivery_internal = DeliveryInternal::findOrFail($id);

        if (request('mode') == "VOID")
        {
            $delivery_internal->status = "VOID";
            $delivery_internal->save();
        }

        $delivery_internal->delete();

        return response()->json(['success' => true]);
    }
}
