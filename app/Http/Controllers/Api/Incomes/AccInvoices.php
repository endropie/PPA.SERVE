<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Income\AccInvoice as Filters;
use App\Models\Income\AccInvoice;
use App\Models\Income\DeliveryOrder;
use App\Models\Income\RequestOrder;
use App\Traits\GenerateNumber;

class AccInvoices extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        $fields = request('fields');
        $fields = $fields ? explode(',', $fields) : [];

        switch (request('mode')) {
            case 'all':
                $acc_invoices = AccInvoice::filter($filters)->get();
                break;

            case 'datagrid':
                $acc_invoices = AccInvoice::with(['created_user'])->filter($filters)
                  ->latest()->get();
                $acc_invoices->each->append(['is_relationship']);
                break;

            default:
                $acc_invoices = AccInvoice::with(['created_user', 'customer'])
                  ->filter($filters)
                  ->latest()->collect();
                $acc_invoices->getCollection()->transform(function($item) {
                    $item->append(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($acc_invoices);
    }

    public function store(Request $request)
    {

        $request->validate([
            'customer_id' => 'required',
            'date' => 'required',
            'order_mode' => 'required',
            'customer_id' => 'required',
            'delivery_orders.*.id' => 'required',
            'request_orders.*.id' => 'required',
        ]);

        $this->DATABASE::beginTransaction();

        $acc_invoice = AccInvoice::create($request->merge([
            'number' => $this->getNextAccInvoiceNumber(),
            'date' => $request->date ?? now(),
        ])->all());

        if ($acc_invoice->customer->is_invoice_request == false) {
            $request->validate(['delivery_orders' => 'required|array']);

            foreach ($request->input('delivery_orders') as $row) {
                $delivery_order = DeliveryOrder::whereNull('acc_invoice_id')->find($row['id']);

                if (!$delivery_order) return $this->error('Delivery undefined! [ID: '. $row['id'] .']');
                if ($delivery_order->status !== 'CONFIRMED') return $this->error('Delivery not confirmed! [SJDO: '. $delivery_order->fullnumber .']');

                $delivery_order->acc_invoice()->associate($acc_invoice);
                $delivery_order->save();
            }
        }

        if ($acc_invoice->customer->is_invoice_request == true) {
            $request->validate(['request_orders' => 'required|array']);

            foreach ($request->input('request_orders') as $row)
            {
                $request_order = RequestOrder::find($row["id"]);
                if (!$request_order) $this->error('['.$row["fullnumber"].'] not invalid!');
                if ($request_order->status != 'CLOSED') $this->error('['.$row["fullnumber"].'] has not CLOSED!');
                if (!$request_order->delivery_orders->count()) $this->error('['.$row["fullnumber"].'] has not deliveries!');

                foreach ($request_order->delivery_orders as $delivery_order)
                {
                    if ($delivery_order->acc_invoice) {
                        return $this->error('Delivery has been invoiced [SJDO: '. $delivery_order->fullnumber .']');
                    }
                    if ($delivery_order->status !== 'CONFIRMED') {
                        return $this->error('Delivery not confirmed! [SJDO: '. $delivery_order->fullnumber .']');
                    }
                    $delivery_order->acc_invoice()->associate($acc_invoice);
                    $delivery_order->save();
                }

                $request_order->acc_invoice_id = $acc_invoice->id;
                $request_order->save();
            }
        }

        // if ($acc_invoice->customer->invoice_mode == 'SEPARATE') {
        //     $acc_invoice2 = AccInvoice::create(
        //         $request->merge([
        //             'number' => $acc_invoice->number . ".JASA",
        //             'date' => $request->date ?? now(),
        //         ])->all()
        //     );
        //     $acc_invoice2->material_invoice()->associate($acc_invoice);
        //     $acc_invoice2->save();
        // }

        $this->DATABASE::commit();
        return response()->json($acc_invoice);
    }

    public function show($id)
    {
        $acc_invoice = AccInvoice::with(['customer'])->findOrFail($id);

        $acc_invoice->setAppends(['deliveries','has_relationship']);

        return response()->json($acc_invoice);
    }

    public function destroy($id)
    {

        $invoice = AccInvoice::findOrFail($id);

        if ($invoice->accurate_model_id)
        {
            $this->DATABASE::beginTransaction();

            $response = $invoice->accurate()->forget();
            if (!$response['s']) {
                return $this->error($response['d']);
            }
            $invoice->accurate_model_id = null;
            $invoice->save();

            $this->DATABASE::commit();
        }

        if ($invoice->service_model_id)
        {
            $this->DATABASE::beginTransaction();

            $invoice2 = $invoice->fresh();
            $invoice2->accurate_primary_key = 'service_model_id';
            $response2 = $invoice2->accurate()->forget();
            if (!$response2['s']) {
                return $this->error($response2['d']);
            }

            $invoice->service_model_id = null;
            $invoice2->save();

            $this->DATABASE::commit();
        }

        $invoice->delete();

        return response()->json(['success' => true]);
    }

    public function confirmed($id)
    {
        $this->DATABASE::beginTransaction();

        $acc_invoice = AccInvoice::findOrFail($id);

        $response = $acc_invoice->accurate()->push();
        if (!$response['s']) {
            return $this->error($response['d']);
        }

        $acc_invoice->invoiced_number = $response['r']['number'];
        $acc_invoice->save();

        if ($acc_invoice->customer->invoice_mode == 'SEPARATE')
        {
            $service = $acc_invoice->fresh();
            $service->setAccuratePrimaryKeyAttribute('service_model_id');

            AccInvoice::registerModelEvent('accurate.pushing', function($record) use ($service) {
                return [
                    'number' => $service->invoiced_number . ".JASA",
                    'is_model_service' => true,
                ];
            });

            $response2 = $service->accurate()->push();
            if (!$response2['s']) {
                return $this->error($response2['d']);
            }
        }

        $acc_invoice->status = 'INVOICED';
        $acc_invoice->save();

        $this->DATABASE::commit();

        // return $response;

        return response()->json(['message' => $response['d'], 'success' => $response['s']]);
    }

    public function forgetInvoice ($id)
    {
        $invoice = AccInvoice::findOrFail($id);

        if ($invoice->service_model_id)
        {
            $response = $invoice->accurate()->forget();
            if (!$response['s']) {
                return response()->json(['message' => $response['d'], 'success' => false], 501);
            }
            $invoice->accurate_model_id = null;
            $invoice->save();
        }

        if ($invoice->service_model_id)
        {
            $invoice2 = $invoice->fresh();
            $invoice2->accurate_primary_key = 'service_model_id';
            $response2 = $invoice2->accurate()->forget();
            if (!$response2['s']) {
                return response()->json(['message' => $response['d'], 'success' => false], 501);
            }

            $invoice->service_model_id = null;
            $invoice2->save();
        }

        $invoice->delete();

        return response()->delete();
    }

}
