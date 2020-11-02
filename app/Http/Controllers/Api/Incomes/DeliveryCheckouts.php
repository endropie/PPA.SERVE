<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter as Filter;
use App\Models\Income\DeliveryCheckout;
use App\Models\Income\DeliveryLoad;
use App\Traits\GenerateNumber;

class DeliveryCheckouts extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $delivery_checkouts = DeliveryCheckout::filter($filter)->latest()->get();
                break;

            case 'datagrid':
                $delivery_checkouts = DeliveryCheckout::with(['vehicle'])->filter($filter)->latest()->get();
                // $delivery_checkouts->each->append(['is_relationship']);
                break;

            default:
                $delivery_checkouts = DeliveryCheckout::with(['created_user','vehicle'])->filter($filter)->latest()->collect();
                $delivery_checkouts->getCollection()->transform(function($item) {
                    // $item->append(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($delivery_checkouts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'date' => 'required',
            'delivery_checkout_loads.*.delivery_load_id' => 'required|distinct'
            // 'delivery_checkout_loads.*.delivery_orders' => length
        ], [
            'delivery_checkout_loads.*.delivery_load_id.distinct' => 'The field has a duplicate value.'
        ]);

        $this->DATABASE::beginTransaction();
        // if(!$request->number) $request->merge(['number'=> $this->getNextDeliveryCheckoutNumber()]);

        $delivery_checkout = DeliveryCheckout::create($request->input());

        $rows = $request->delivery_checkout_loads;
        for ($i=0; $i < count($rows); $i++) {
            $load = DeliveryLoad::find($rows[$i]['delivery_load_id']);
            $delivery_checkout->delivery_loads()->save($load);
        }

        $this->DATABASE::commit();
        return response()->json($delivery_checkout);
    }

    public function show($id)
    {
        $delivery_checkout = DeliveryCheckout::with([
            'vehicle',
            'delivery_loads.customer',
        ])->findOrFail($id);

        $delivery_checkout->append(['has_relationship']);

        return response()->json($delivery_checkout);
    }

    public function update(Request $request, $id)
    {
        $this->error('INVALID METHOD');

        $this->DATABASE::beginTransaction();

        $delivery_checkout = DeliveryCheckout::findOrFail($id);

        $this->DATABASE::commit();
        return response()->json($delivery_checkout);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_checkout = DeliveryCheckout::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        $delivery_checkout->delete();

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }
}
