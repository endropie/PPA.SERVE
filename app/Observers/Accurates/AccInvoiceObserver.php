<?php

namespace App\Observers\Accurates;

use App\Models\Income\AccInvoice as Model;

class AccInvoiceObserver
{
    public function pushing(Model $model, $record)
    {
        // abort(501, 'FIXED');
        if (!$model->request_order->customer->invoice_mode) {
            abort(501, 'Invoice Mode Undefined, Please enter customer invoice mode!');
        }

        $mode = $model->request_order->customer->invoice_mode;

        $detailItems = $model->acc_invoice_items
        ->groupBy(function($item) {return $item->item_id . '-'. $item->request_order_item->price;})->values()
        ->map(function ($details, $key) use ($mode){
            $indexed = (int) $mode === 'DETAIL' ? $key : ($key*2);
            $quantity = collect($details)->sum('quantity');
            $detail = $details->first();

            $detailName = $detail->item->part_name;
            if ($detail->item->part_name != $detail->item->part_number) $detailName .= " (".$detail->item->part_number.")";

            $price = $detail->request_order_item
                ? $detail->request_order_item->price
                : $detail->item->price;

            return $mode !== 'DETAIL'
            ? [
                "detailItem[$indexed].itemNo" => (string) $detail->item->code,
                "detailItem[$indexed].quantity" => (double) $quantity,
                "detailItem[$indexed].unitPrice" => (double) $price,
                "detailItem[". ($indexed+1) ."].detailName" => (string) $detailName,
              ]
            : [
                "detailItem[$indexed].itemNo" => (string) $detail->item->code,
                "detailItem[$indexed].quantity" => (double) $quantity,
                "detailItem[$indexed].unitPrice" => (double) $price,
                "detailItem[$indexed].detailName" => (string) "[MATERIAL] ". $detailName,

                "detailItem[". ($indexed+1) ."].itemNo" => (string) $detail->item->code,
                "detailItem[". ($indexed+1) ."].quantity" => (double) $quantity,
                "detailItem[". ($indexed+1) ."].unitPrice" => (double) $price,
                "detailItem[". ($indexed+1) ."].detailName" => (string) "[JASA] ". $detailName,
              ];
        })
        ->collapse()->toArray();

        return array_merge($record, $detailItems);
    }
}
