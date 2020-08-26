<?php

namespace App\Observers\Accurates;

use App\Models\Income\AccInvoice as Model;

class AccInvoiceObserver
{

    protected $summary_service = 0;

    public function pushing(Model $model, $record)
    {
        $mode = $model->customer->invoice_mode;
        $inMaterial = (boolean) !$model->service_invoice_id;

        $detailItems = $model->delivery_items
        ->groupBy(function($detail) {
            if ($detail->request_order_item) {
                if ($detail->request_order_item->request_order->order_mode === 'PO') {
                    return $detail->item_id . '-'. $detail->request_order_item->price;
                }
            }
            return $detail->item_id;
        })
        ->values()
        ->map(function ($details, $key) use ($mode, $inMaterial) {

            $quantity = collect($details)->sum('quantity');
            $detail = $details->first();

            $detailName = $detail->item->part_name;
            $subnameMode = setting()->get('item.subname_mode', null);
            $subnameLabel = setting()->get('item.subname_label', null);
            $detailNotes = !$subnameMode ? null : (string) $subnameLabel ." ". $detail->item->part_subname;

            $useTax1 = (boolean) $detail->item->customer->with_ppn;
            $useTax3 = (boolean) $detail->item->customer->with_pph;

            if ($detail->item->part_name != $detail->item->part_number) $detailName .= " (".$detail->item->part_number.")";

            $senService = (double) ($detail->item->customer->sen_service) / 100;

            $price = (double) $detail->item->price;

            if ($detail->request_order_item) {
                if ($detail->request_order_item->request_order->order_mode === 'PO') {
                    $price = $detail->request_order_item->price;
                }
            }

            if ($mode == 'SUMMARY') {

                if ($detail->item->customer->exclude_service) {
                    $v = (100 + $detail->item->customer->sen_service) / 100;
                    $priceMaterial = ceil($price / $v * 100) / 100;
                    $priceService  = round($price - $priceMaterial, 2);
                }
                else {
                    $priceMaterial = ceil($price * (1 - $senService) *100) / 100;
                    $priceService  = round($price - $priceMaterial, 2);
                }

                return [
                    "detailItem[$key].itemNo" => 'ITEM-MATERIAL',
                    "detailItem[$key].detailName" => (string) $detailName,
                    "detailItem[$key].detailNotes" => $detailNotes,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) $priceMaterial,
                    // "detailItem[$key].useTax1" => (boolean) $useTax1,
                    // "detailItem[$key].useTax3" => (boolean) $useTax3,
                    "SUMMARY_JASA" => (double) ($quantity * $priceService)
                ];
            }
            else if ($mode == 'DETAIL') {
                $doublekey = (int) $key*2;
                $senService = (double) ($detail->item->customer->sen_service / 100);
                $priceMaterial = ceil($price * (1 - $senService) *100) / 100;
                $priceService  = round($price - $priceMaterial, 2);

                return [
                    "detailItem[$doublekey].itemNo" => 'ITEM-MATERIAL',
                    "detailItem[$doublekey].detailName" => (string) "[MATERIAL] ". $detailName,
                    "detailItem[$doublekey].detailNotes" => $detailNotes,
                    "detailItem[$doublekey].quantity" => (double) $quantity,
                    "detailItem[$doublekey].unitPrice" => (double) $priceMaterial,
                    // "detailItem[$doublekey].useTax1" => (boolean) $useTax1,
                    // "detailItem[$doublekey].useTax3" => (boolean) $useTax3,

                    "detailItem[". ($doublekey+1) ."].itemNo" => 'ITEM-JASA',
                    "detailItem[". ($doublekey+1) ."].detailName" => (string) "[JASA] ". $detailName,
                    "detailItem[". ($doublekey+1) ."].detailNotes" => $detailNotes,
                    "detailItem[". ($doublekey+1) ."].quantity" => (double) $quantity,
                    "detailItem[". ($doublekey+1) ."].unitPrice" => (double) $priceService,
                    // "detailItem[". ($doublekey+1) ."].useTax1" => (boolean) $useTax1,
                    // "detailItem[". ($doublekey+1) ."].useTax3" => (boolean) $useTax3,
                ];
            }
            else if ($mode == 'SEPARATE') {
                $senService = (double) ($detail->item->customer->sen_service / 100);
                $priceMaterial = ceil($price * (1 - $senService) *100) / 100;
                $priceService  = round($price - $priceMaterial, 2);
                $detailPrice = $inMaterial ? $priceMaterial : $priceService;
                $detailNo = ($inMaterial ? 'ITEM-MATERIAL' : 'ITEM-JASA');
                $detailName = ($inMaterial ? '[MATERIAL] ' : '[JASA] '). $detailName;

                return [
                    "detailItem[$key].itemNo" => $detailNo,
                    "detailItem[$key].detailName" => (string) $detailName,
                    "detailItem[$key].detailNotes" => $detailNotes,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) ($detailPrice),
                    // "detailItem[$key].useTax1" => (boolean) $useTax1,
                    // "detailItem[$key].useTax3" => (boolean) $useTax3,
                ];
            }
            else if ($mode == 'JOIN') {
                return [
                    "detailItem[$key].itemNo" => 'ITEM-JASA',
                    "detailItem[$key].detailName" => (string) $detailName,
                    "detailItem[$key].detailNotes" => $detailNotes,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) $price,
                    // "taxable" => true,
                    // "detailItem[$key].useTax1" => (boolean) $useTax1,
                    // "detailItem[$key].useTax3" => (boolean) $useTax3,
                    // "taxDate" => '28/08/2020',
                    // "taxNumber" => '123',
                    // "documentCode" => 'DIGUNGGUNG',
                    // "taxType" => 'BKN_PEMUNGUT_PPN',
                ];
            }
            else {
                abort(501, "Invoice mode [". $detail->item->customer->invoice_mode ."] failed" );
            }
        });

        if ($mode == 'SUMMARY')
        {
            $key = $detailItems->count();
            $sum = (double) $detailItems->sum('SUMMARY_JASA');
            $detailItems = $detailItems->push([
                "detailItem[$key].itemNo" => 'ITEM-JASA',
                "detailItem[$key].detailName" => 'JASA Part',
                "detailItem[$key].quantity" => 1,
                "detailItem[$key].unitPrice" => (double) $sum,
            ]);
        }

        $detailItems = $detailItems->collapse()->toArray();

        return array_merge($record, $detailItems);
    }
}
