<?php

namespace App\Observers\Accurates;

use App\Models\Income\AccInvoice as Model;

class AccInvoiceObserver
{

    public function pushing(Model $model, $record)
    {
        $mode = $model->customer->invoice_mode;
        $serviceModel = (boolean) ($record['is_model_service'] ?? false);

        $isPriceCategory = $model->customer->invoice_category_price;

        $detailItems = $model->delivery_items
        ->sortBy(function ($detail) use ($isPriceCategory) {
            return $detail['item'][ $isPriceCategory ? 'category_item_price_id' : 'code'];
        })
        ->groupBy($isPriceCategory ? 'category_item_price_id' : 'item_id')
        ->values();


        $detailItems = $detailItems->map(function ($details, $key) use ($mode, $serviceModel, $isPriceCategory, $detailItems) {

            $quantity = collect($details)->sum('quantity');
            $detail = $details->first();

            if ($isPriceCategory && !$detail->item->category_item_price) abort(501, "INVOICED FAILED. ". $detail->item->part_name ." [#". $detail->item->id ."] not has category price");

            $detailName = $isPriceCategory ? $detail->item->category_item_price->name : $detail->item->part_name;
            $subnameMode = setting()->get('item.subname_mode', null);
            $subnameLabel = setting()->get('item.subname_label', null);
            $detailNotes = !$subnameMode || $isPriceCategory ? null : (string) $subnameLabel ." ". $detail->item->part_subname;

            $unit = ucfirst($detail->item->unit->code);

            $useTax1 = (boolean) $detail->item->customer->with_ppn;
            $useTax3 = (boolean) $detail->item->customer->with_pph;

            if (!$isPriceCategory && $detail->item->part_name != $detail->item->part_number) $detailName .= " (".$detail->item->part_number.")";

            $senService = (double) ($detail->item->customer->sen_service) / 100;

            $price = (double) ($isPriceCategory
                ? $this->number_normalize($detail->item->category_item_price->price, 7)
                : $this->number_normalize($detail->item->price, 7)
            );

            if ($mode == 'SUMMARY') {

                if ($detail->item->customer->exclude_service) {
                    $v = (100 + $detail->item->customer->sen_service) / 100;
                    $priceMaterial = $this->number_normalize($price / $v, 7, true);
                    $priceService  = $this->number_normalize($price - $priceMaterial);
                }
                else {
                    $priceMaterial = $this->number_normalize($price * (1 - $senService), 7, true);
                    $priceService  = $this->number_normalize($price - $priceMaterial, 7);
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
                $length = $detailItems->count();
                $senService = (double) ($detail->item->customer->sen_service / 100);
                $priceMaterial = $this->number_normalize($price * (1 - $senService), 7, true);
                $priceService  = $this->number_normalize($price - $priceMaterial, 7);

                return [

                    "detailItem[$key].itemNo" => 'ITEM-MATERIAL',
                    "detailItem[$key].detailName" => (string) "[MATERIAL] ". $detailName,
                    "detailItem[$key].detailNotes" => $detailNotes,
                    "detailItem[$key].quantity" => (double) $quantity,
                    "detailItem[$key].unitPrice" => (double) $priceMaterial,
                    "detailItem[$key].itemUnitName" => (string) $unit,

                    "detailItem[". ($key+$length) ."].itemNo" => 'ITEM-JASA',
                    "detailItem[". ($key+$length) ."].detailName" => (string) "[JASA] ". $detailName,
                    "detailItem[". ($key+$length) ."].detailNotes" => $detailNotes,
                    "detailItem[". ($key+$length) ."].quantity" => (double) $quantity,
                    "detailItem[". ($key+$length) ."].unitPrice" => (double) $priceService,
                    "detailItem[". ($key+$length) ."].itemUnitName" => (string) $unit,
                ];
            }
            else if ($mode == 'SEPARATE') {
                $senService = (double) ($detail->item->customer->sen_service / 100);
                $priceMaterial = $this->number_normalize($price * (1 - $senService), 7, true);
                $priceService  = $this->number_normalize($price - $priceMaterial, 7);
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
            $sum = (double) $this->number_normalize($detailItems->sum('SUMMARY_JASA'), 7);
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

        if (!$model->accurate_model_id) {
            $record = array_merge($record, ["saveAsStatusType" => "DRAFT"]);
        }

        return array_merge($record, $detailItems);
    }

    protected function number_normalize($value, $max = 7, $ROUNDUP = false)
    {
        $pow = (double) pow(10, $max);
        return strlen(explode('.', (string) $value)[1] ?? "") > $max && $ROUNDUP
            ? round(ceil($value * $pow) / $pow, $max)
            : round(($value * $pow) / $pow, $max);
    }
}
