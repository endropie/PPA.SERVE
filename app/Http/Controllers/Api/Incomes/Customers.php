<?php

namespace App\Http\Controllers\Api\Incomes;

// use App\Filters\Filter as Filter;
use App\Filters\Income\Customer as Filter;
use App\Http\Requests\Income\Customer as Request;
use App\Http\Controllers\ApiController;

use App\Models\Income\Customer;

class Customers extends ApiController
{
    public function index(Filter $filters)
    {
        switch (request('mode')) {
            case 'all':
                $customers = Customer::filter($filters)->getCollect();
                break;

            case 'datagrid':
                $customers = Customer::filter($filters)->latest()->get();

                break;

            default:
                $customers = Customer::filter($filters)->collect();
                break;
        }

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $customer = Customer::create($request->all());

        return response()->json($customer);
    }

    public function show($id)
    {
        $customer = Customer::with(['customer_contacts','customer_trips'])->findOrFail($id);
        $customer->is_editable = (!$customer->is_related);

        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->input());

        // Delete all contacts on before the customer updated!
        $customer->customer_contacts()->delete();

        $pre = $request->customer_contacts;
        for ($i=0; $i < count($pre); $i++) {

            // create contacts on the customer updated!
            $customer->customer_contacts()->create($pre[$i]);
        }

        // Delete all trips on before the customer updated!
        $customer->customer_trips()->delete();

        $trips = $request->customer_trips;
        for ($i=0; $i < count($trips); $i++) {

            // create trips on the customer updated!
            $customer->customer_trips()->create($trips[$i]);
        }

        return response()->json($customer);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json(['success' => true]);
    }

    public function push ($id)
    {
        if ($id === 'all') {
            $customers = Customer::whereNull('accurate_model_id')->get();
            return $customers->map(function($customer) {
                $push = $customer->accurate()->push();
                return collect($push)->except('r');
            });
        }
        else {
            $customer = Customer::findOrFail($id);
            return $customer->accurate()->push();
        }
    }
}
