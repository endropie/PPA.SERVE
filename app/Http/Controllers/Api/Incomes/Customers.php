<?php

namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\Customer as Request;
use App\Http\Controllers\ApiController;

use App\Models\Incomes\Customer;

class Customers extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $customers = Customer::filterable()->get();    
                break;

            case 'datagrid':
                $customers = Customer::filterable()->get();
                
                break;

            default:
                $customers = Customer::collect();                
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
        $customer = Customer::findOrFail($id);
        $customer->is_editable = (!$customer->is_related);

        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $customer->update($request->input());

        return response()->json($customer);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json(['success' => true]);
    }
}
