<?php

namespace App\Observers\Accurates;

use App\Models\Income\AccInvoice as Model;

class AccInvoiceObserver
{
    public function pushing(Model $model, $record)
    {
        if (!$model->request_order->customer->invoice_mode) {
            abort(501, 'Invoice mode Undefined, Please enter customer invoice mode!');
        }

        $mode = $model->request_order->customer->invoice_mode;
        $service = $model->serviceMode;

        // abort(501, json_encode($mode));

        $detailItems = $model->acc_invoice_items
        ->groupBy(function($item) {return $item->item_id . '-'. $item->request_order_item->price;})
        ->values()
        ->map(function ($details, $key) use ($mode, $service) {
            $dtlKey = (int) $mode === 'DETAIL' ? $key : ($key*2);
            $quantity = collect($details)->sum('quantity');
            $detail = $details->first();

            $detailName = $detail->item->part_name;
            if ($detail->item->part_name != $detail->item->part_number) $detailName .= " (".$detail->item->part_number.")";

            $price = $detail->item->price;

            if ($detail->request_order_item) {
                if ($detail->request_order_item->request_order->order_mode === 'PO') {
                    $price = $detail->request_order_item->price;
                }
            }

            if ($mode == 'DETAIL') {
                $servicePersen = (double) ($detail->item->customer->pph_service / 100);
                return [
                    "detailItem[$dtlKey].itemNo" => (string) $detail->item->code,
                    "detailItem[$dtlKey].quantity" => (double) $quantity,
                    "detailItem[$dtlKey].unitPrice" => (double) $price * (1 - $servicePersen),
                    "detailItem[$dtlKey].detailName" => (string) "[MATERIAL] ". $detailName,

                    "detailItem[". ($dtlKey+1) ."].itemNo" => (string) $detail->item->code,
                    "detailItem[". ($dtlKey+1) ."].quantity" => (double) $quantity,
                    "detailItem[". ($dtlKey+1) ."].unitPrice" => (double) $price * $servicePersen,
                    "detailItem[". ($dtlKey+1) ."].detailName" => (string) "[JASA] ". $detailName,
                ];
            }
            else if ($mode == 'SEPARATE') {
                $persen = (double) ($detail->item->customer->pph_service / 100);
                if (!$service) $persen = (1 - $persen);
                return [
                    "detailItem[$key].itemNo" => (string) $detail->item->code,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) $price * ($persen),
                    "detailItem[$key].detailName" => (string) $detailName
                ];
            }
            else if ($mode == 'JOIN') {
                return [
                    "detailItem[$key].itemNo" => (string) $detail->item->code,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) $price,
                    "detailItem[$key].detailName" => (string) $detailName,
                ];
            }
            else {
                abort(501, "Invoice mode [". $detail->item->customer->invoice_mode ."] failed" );
            }
        })
        ->collapse()->toArray();

        return array_merge($record, $detailItems);
    }
}
