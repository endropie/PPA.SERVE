<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter as Filter;
use App\Models\Income\DeliveryCheckout;
use App\Models\Income\DeliveryLoad;
use App\Models\Income\DeliveryOrder;
use App\Models\Warehouse\DeportationGood;
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

    public function rutes(Filter $filter)
    {
        $delivery_checkouts = DeliveryCheckout::filter($filter)->whereHas('rute')->latest()->collect();
        $delivery_checkouts->getCollection()->transform(function($item) {
            return [
                'id' => $item->id,
                'date' => $item->date,
                'description' => $item->description,
                'rute' => $item->rute()->get(['id', 'name'])->first(),
                'vehicle' => $item->vehicle()->get(['id', 'number'])->first(),
            ];
            return $item;
        });

        return response()->json($delivery_checkouts);
    }

    public function rute($id)
    {
        $delivery_checkout = DeliveryCheckout::findOrFail($id);

        return response()->json([
            'id' => $delivery_checkout->id,
            'date' => $delivery_checkout->date,
            'description' => $delivery_checkout->description,
            'rute' => $delivery_checkout->rute()->get(['id', 'name'])->first(),
            'vehicle' => $delivery_checkout->vehicle()->get(['id', 'number'])->first(),
        ]);
    }

    public function store(Request $request)
    {
        $validateLoad = sizeof($request->delivery_checkout_internals) || sizeof($request->delivery_checkout_deportations)
            ? 'nullable' : 'required|min:1';

        $request->validate([
            'vehicle_id' => 'required',
            'date' => 'required',
            'delivery_checkout_loads' => $validateLoad,
            'delivery_checkout_loads.*.delivery_load_id' => 'required|distinct',
            'delivery_checkout_internals.*.delivery_order_id' => 'required|distinct',
            'delivery_checkout_deportations.*.deportation_good_id' => 'required|distinct',
        ], [
            'delivery_checkout_loads.*.delivery_load_id.distinct' => 'The field has a duplicate value.'
        ]);

        $this->DATABASE::beginTransaction();

        $delivery_checkout = DeliveryCheckout::create($request->input());

        $rows = $request->delivery_checkout_loads;
        for ($i=0; $i < count($rows); $i++) {
            $load = DeliveryLoad::find($rows[$i]['delivery_load_id']);
            $delivery_checkout->delivery_loads()->save($load);
        }

        $rows = $request->delivery_checkout_internals;
        for ($i=0; $i < count($rows); $i++) {
            $internal = DeliveryOrder::find($rows[$i]['delivery_order_id']);
            $delivery_checkout->delivery_order_internals()->save($internal);
        }

        $rows = $request->delivery_checkout_deportations;
        for ($i=0; $i < count($rows); $i++) {
            $deportation = DeportationGood::find($rows[$i]['deportation_good_id']);
            $delivery_checkout->deportation_goods()->save($deportation);
        }

        $delivery_checkout->setCommentLog("DELIVERY CHECKOUT [$delivery_checkout->fullnumber] has been created!");

        $this->DATABASE::commit();
        return response()->json($delivery_checkout);
    }

    public function show($id)
    {
        $delivery_checkout = DeliveryCheckout::with([
            'vehicle',
            'delivery_loads.customer',
            'delivery_order_internals.customer',
            'deportation_goods.customer',
        ])->findOrFail($id);

        $delivery_checkout->append(['has_relationship']);

        return response()->json($delivery_checkout);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_checkout = DeliveryCheckout::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        $delivery_checkout->delete();

        $action = ($mode == "VOID") ? 'voided' : 'deleted';

        $delivery_checkout->setCommentLog("DELIVERY CHECKOUT [$delivery_checkout->fullnumber] has been $action !");

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }
}
