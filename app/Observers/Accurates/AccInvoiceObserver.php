<?php

namespace App\Observers\Accurates;

use App\Models\Income\AccInvoice as Model;

class AccInvoiceObserver
{

    public function pushing(Model $model, $record)
    {
        $mode = $model->customer->invoice_mode;
        $serviceModel = (boolean) ($record['is_model_service'] ?? false);

        $detailItems = $model->delivery_items
        ->sortBy(function ($detail) {
            return $detail['item']['code'];
        })
        ->groupBy('item_id')
        ->values()
        ->map(function ($details, $key) use ($mode, $serviceModel) {

            $quantity = collect($details)->sum('quantity');
            $detail = $details->first();

            $detailName = $detail->item->part_name;
            $subnameMode = setting()->get('item.subname_mode', null);
            $subnameLabel = setting()->get('item.subname_label', null);
            $detailNotes = !$subnameMode ? null : (string) $subnameLabel ." ". $detail->item->part_subname;

            $unit = ucfirst($detail->item->unit->code);

            $useTax1 = (boolean) $detail->item->customer->with_ppn;
            $useTax3 = (boolean) $detail->item->customer->with_pph;

            if ($detail->item->part_name != $detail->item->part_number) $detailName .= " (".$detail->item->part_number.")";

            $senService = (double) ($detail->item->customer->sen_service) / 100;

            $price = (double) round($detail->item->price, 2);

            if ($mode == 'SUMMARY') {

                if ($detail->item->customer->exclude_service) {
                    $v = (100 + $detail->item->customer->sen_service) / 100;
                    $priceMaterial = round(ceil($price / $v * 10000) / 10000, 2);
                    $priceService  = round($price - $priceMaterial, 2);
                }
                else {
                    $priceMaterial = round(ceil($price * (1 - $senService) * 10000) / 10000, 2);
                    $priceService  = round($price - $priceMaterial, 2);
                }

                return [
                    "detailItem[$key].itemNo" => 'ITEM-MATERIAL',
                    "detailItem[$key].detailName" => (string) $detailName,
                    "detailItem[$key].detailNotes" => $detailNotes,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) $priceMaterial,
                    "detailItem[$key].itemUnitName" => (string) $unit,
                    "SUMMARY_JASA" => (double) ($quantity * $priceService)
                ];
            }
            else if ($mode == 'DETAIL') {
                $doublekey = (int) $key*2;
                $senService = (double) ($detail->item->customer->sen_service / 100);
                $priceMaterial = round(ceil($price * (1 - $senService) * 10000) / 10000, 2);
                $priceService  = round($price - $priceMaterial, 2);

                return [
                    "detailItem[$doublekey].itemNo" => 'ITEM-MATERIAL',
                    "detailItem[$doublekey].detailName" => (string) "[MATERIAL] ". $detailName,
                    "detailItem[$doublekey].detailNotes" => $detailNotes,
                    "detailItem[$doublekey].quantity" => (double) $quantity,
                    "detailItem[$doublekey].unitPrice" => (double) $priceMaterial,
                    "detailItem[$doublekey].itemUnitName" => (string) $unit,

                    "detailItem[". ($doublekey+1) ."].itemNo" => 'ITEM-JASA',
                    "detailItem[". ($doublekey+1) ."].detailName" => (string) "[JASA] ". $detailName,
                    "detailItem[". ($doublekey+1) ."].detailNotes" => $detailNotes,
                    "detailItem[". ($doublekey+1) ."].quantity" => (double) $quantity,
                    "detailItem[". ($doublekey+1) ."].unitPrice" => (double) $priceService,
                    "detailItem[". ($doublekey+1) ."].itemUnitName" => (string) $unit,
                ];
            }
            else if ($mode == 'SEPARATE') {
                $senService = (double) ($detail->item->customer->sen_service / 100);
                $priceMaterial = round(ceil($price * (1 - $senService) * 10000) / 10000, 2);
                $priceService  = round($price - $priceMaterial, 2);
                $detailPrice = $serviceModel ? $priceService : $priceMaterial;
                $detailNo = ($serviceModel ? 'ITEM-JASA' : 'ITEM-MATERIAL');
                $detailName = ($serviceModel ? '[JASA] ' : '[MATERIAL] '). $detailName;

                return [
                    "detailItem[$key].itemNo" => $detailNo,
                    "detailItem[$key].detailName" => (string) $detailName,
                    "detailItem[$key].detailNotes" => $detailNotes,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) ($detailPrice),
                    "detailItem[$key].itemUnitName" => (string) $unit,
                ];
            }
            else if ($mode == 'JOIN') {
                return [
                    "detailItem[$key].itemNo" => 'ITEM-JASA',
                    "detailItem[$key].detailName" => (string) $detailName,
                    "detailItem[$key].detailNotes" => $detailNotes,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) $price,
                    "detailItem[$key].itemUnitName" => (string) $unit,
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
                "detailItem[$key].itemNo" => 'ITEM-JASA-TOTAL',
                "detailItem[$key].detailName" => 'JASA Part',
                "detailItem[$key].quantity" => 1,
                "detailItem[$key].unitPrice" => (double) $sum,
            ]);
        }

        $detailItems = $detailItems->collapse()->toArray();

        if ($branchId = env('ACCURATE_BRANCH_ID', null))
        {
            $record = array_merge($record, ['branchId' => $branchId]);
        }

        $record = array_merge($record, [
            // "saveAsStatusType" => "UNAPPROVED",
            // "paymentTermName" => "net 30",
        ]);

        return array_merge($record, $detailItems);
    }
}
