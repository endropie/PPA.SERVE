<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Filters\Warehouse\OutgoingGood as Filters;
use App\Http\Requests\Warehouse\OutgoingGood as Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Request as RequestsRequest;
use App\Models\Common\Item;
use App\Models\Warehouse\OutgoingGood;
use App\Models\Warehouse\OutgoingGoodVerification;
use App\Models\Income\RequestOrder;
use App\Models\Income\RequestOrderItem;
use App\Traits\GenerateNumber;

// use function Safe\substr;

class OutgoingGoods extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $outgoing_goods = OutgoingGood::filter($filters)->get();
                break;

            case 'datagrid':
                $outgoing_goods = OutgoingGood::with(['customer'])
                    ->filter($filters)
                    ->latest()->get();
                $outgoing_goods->each->append(['is_relationship']);
                break;

            default:
                $outgoing_goods = OutgoingGood::with([
                    'created_user',
                    'delivery_orders' => function ($q) {
                        $q->select(['id', 'outgoing_good_id', 'number', 'revise_number']);
                    },
                    'customer' => function ($q) {
                        $q->select(['id', 'name']);
                    }
                ])->filter($filters)
                    ->latest()->collect();

                $outgoing_goods->getCollection()->transform(function ($item) {
                    $item->append(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($outgoing_goods);
    }

    public function store(Request $request)
    {
        $this->request = $request;
        $this->DATABASE::beginTransaction();

        if (!$request->number) $request->merge(['number' => $this->getNextOutgoingGoodNumber()]);

        $outgoing_good = OutgoingGood::create($request->all());

        $rows = $request->outgoing_good_items;

        if (count($rows) <= 0) abort(501, 'Part detail not found!');
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // create item row on the incoming Goods updated!
            $detail = $outgoing_good->outgoing_good_items()->create($row);

            $PDO = $outgoing_good->transaction == "RETURN" ? 'PDO.RET' : 'PDO.REG';
            $detail->item->transfer($detail, $detail->unit_amount, null, $PDO);
            $detail->item->transfer($detail, $detail->unit_amount, null, 'VDO');
        }

        if ($outgoing_good->transaction == "REGULER" && $outgoing_good->customer->order_mode == "ACCUMULATE")
        {
            $this->storeRequestOrder($outgoing_good->fresh());
        }
        else {
            $this->storeDeliveryOrder($outgoing_good->fresh());
        }

        OutgoingGoodVerification::whereNull('validated_at')
            ->whereHas('item', function ($query) use ($outgoing_good) {
                return $query->where('customer_id', $outgoing_good->customer_id);
            })
            ->update(['validated_at' => now()]);

        $this->DATABASE::commit();
        return response()->json($outgoing_good);
    }

    public function show($id)
    {
        $outgoing_good = OutgoingGood::with([
            'customer',
            'delivery_orders',
            'outgoing_good_items.item.item_units',
            'outgoing_good_items.unit'
        ])->withTrashed()->findOrFail($id);

        $outgoing_good->append(['has_relationship']);

        return response()->json($outgoing_good);
    }

    public function storeRequestOrder($outgoing_good)
    {

        $request_order = RequestOrder::where('customer_id', $outgoing_good->customer_id)
            ->where('order_mode', $outgoing_good->customer->order_mode)
            ->where('status', 'OPEN')
            ->whereMonth('date', substr($outgoing_good->date, 5, 2))
            ->whereYear('date', substr($outgoing_good->date, 0, 4))
            ->oldest()->first();

        if (!$request_order) {
            $request_order = new RequestOrder;
            $order_mode = $outgoing_good->customer->order_mode;

            $request_order->date  = $outgoing_good->date;
            $request_order->customer_id = $outgoing_good->customer_id;
            $request_order->order_mode   = $order_mode;
            $request_order->transaction   = 'REGULER';

            $begin = \Carbon::parse($outgoing_good->date)->startOfMonth()->format('Y-m-d');
            $until = \Carbon::parse($outgoing_good->date)->endOfMonth()->format('Y-m-d');
            $request_order->description   = "ACCUMULATE P/O. $begin - $until";
            // For model update
            if (!$request_order->id) {
                $request_order->number = $this->getNextRequestOrderNumber($outgoing_good->date);
            }
            $request_order->save();
        }

        $prefix_code = $outgoing_good->customer->code ?? "C$outgoing_good->customer_id";

        $delivery_order = $outgoing_good->delivery_orders()->create([
            'number' => $this->getNextSJDeliveryNumber($outgoing_good->date),
            'indexed_number' => $this->getNextSJDeliveryIndexedNumber($outgoing_good->date, $prefix_code),
            'transaction' =>  $outgoing_good->transaction,
            'customer_id' => $outgoing_good->customer_id,
            'customer_name' => $outgoing_good->customer_name,
            'customer_phone' => $outgoing_good->customer_phone,
            'customer_address' => $outgoing_good->customer_address,
            'description' => $outgoing_good->description,
            'customer_note' => $outgoing_good->customer_note,
            'date' => $outgoing_good->date,
            'vehicle_id' => $outgoing_good->vehicle_id,
            'rit' => $outgoing_good->rit,
        ]);

        $rows = $outgoing_good->outgoing_good_items
            ->groupBy('item_id')
            ->map(function ($group) {
                return [
                    'item_id' => $group[0]->item_id,
                    'quantity' => $group->sum('unit_amount'),
                    'price' => $group[0]->item->price,
                    'unit_id' => $group[0]->item->unit_id,
                    'unit_rate' => 1,
                ];
            });

        foreach ($rows as $row) {
            $detail = $request_order->request_order_items()->create($row);

            ## Setup unit price
            $detail->price = ($detail->item && $detail->item->price)
                ? $detail->unit_rate * $detail->item->price : 0;
            $detail->save();

            $delivery_order_item = $delivery_order->delivery_order_items()->create($row);
            $delivery_order_item->request_order_item()->associate($detail);
            $delivery_order_item->save();

            $delivery_order_item->item->transfer($delivery_order_item, $delivery_order_item->unit_amount, null, 'FG');
        }

        $delivery_order->delivery_order_items->each->calculate();
        $delivery_order->request_order()->associate($request_order);
        $delivery_order->save();
    }

    public function storeDeliveryOrder($outgoing_good)
    {
        $list = []; $over=[];
        $request_order_items = RequestOrderItem::whereRaw('(quantity * unit_rate) > amount_delivery')
            ->whereHas('request_order', function ($query) use ($outgoing_good) {
                $order_mode = $outgoing_good->transaction == 'RETURN' ? 'NONE' : $outgoing_good->customer->order_mode;
                return $query->where('status', 'OPEN')
                    ->where('order_mode', $order_mode)
                    ->where('transaction', $outgoing_good->transaction)
                    ->where('customer_id', $outgoing_good->customer_id);
            })->get();

        $request_order_items = $request_order_items
            ->filter(function ($x) use ($outgoing_good) {
                $request_order = $x->request_order;
                if ($request_order->date && $outgoing_good->date < $request_order->date) return false;
                if ($request_order->actived_date && $outgoing_good->date > $request_order->actived_date) return false;
                return true;
                // if ($x->request_order->order_mode != 'PO') return true;
                // return $outgoing_good->date >= $x->request_order->date
                //     && $outgoing_good->date <= $x->request_order->actived_date;
            })
            ->map(function ($detail) {
                $detail->sortin = $detail->request_order->date ." ". $detail->request_order->created_at;
                return $detail;
            })
            ->sortBy('sortin');

        $outer = $outgoing_good->outgoing_good_items
            ->groupBy('item_id')
            ->map(function ($group) {
                return $group->sum('unit_amount');
            });

        foreach ($request_order_items as $detail) {
            if (isset($outer[$detail->item_id])) {

                $max_amount = $outer[$detail->item_id];
                $unit_amount = $detail->unit_amount - $detail->amount_delivery;
                $unit_amount = ($max_amount < $unit_amount ? $max_amount : $unit_amount);

                $outer[$detail->item_id] -= $unit_amount;

                if ($unit_amount > 0) {
                    $RO = $detail->request_order_id;
                    $DTL = $detail->id;

                    $list[$RO][$DTL] = [
                        'item_id' => $detail->item_id,
                        'quantity' => $unit_amount,
                        'price' => $detail->item->price ?? 0,
                        'unit_id' => $detail->item->unit_id,
                        'unit_rate' => 1,
                    ];
                }
            }
        }

        foreach ($outer as $key => $amount) {
            if (round($amount) > 0) {
                $item = Item::find($key);
                $label = ($item->part_number ?? $key);

                ## [SJID] SJ-INTERNAL (HOLD)
                // if ($outgoing_good->transaction == 'REGULER' && $outgoing_good->customer->order_mode == 'PO') {
                //     $this->request->validate(['delivery_order_intern' => 'required']);
                //     if ($this->request->delivery_order_intern) {
                //         $over[$key] = $amount;
                //         continue;
                //     }
                // }
                $this->error("OVER OUTGOING BY PO [$label:$amount]");
            }
        }

        if (sizeof($over)) {
            $prefix_code = $outgoing_good->customer->code ?? "C$outgoing_good->customer_id";
            $delivery_order = $outgoing_good->delivery_orders()->create([
                'number' => $this->getNextSJInternalNumber($outgoing_good->date),
                'indexed_number' => $this->getNextSJDeliveryIndexedNumber($outgoing_good->date, $prefix_code),
                'transaction' =>  $outgoing_good->transaction,
                'customer_id' => $outgoing_good->customer_id,
                'customer_name' => $outgoing_good->customer_name,
                'customer_phone' => $outgoing_good->customer_phone,
                'customer_address' => $outgoing_good->customer_address,
                'customer_note' => $outgoing_good->customer_note,
                'description' => $outgoing_good->description,
                'date' => $outgoing_good->date,
                'vehicle_id' => $outgoing_good->vehicle_id,
                'rit' => $outgoing_good->rit,
                'is_internal' => true,
            ]);

            foreach ($over as $key => $amount) {
                $item = Item::find($key);
                $detail = $delivery_order->delivery_order_items()->create([
                    'item_id' => $item->id,
                    'quantity' => $amount,
                    'price' => $item->price ?? 0,
                    'unit_id' => $item->unit->id,
                    'unit_rate' => 1
                ]);
                $detail->item->transfer($detail, $detail->unit_amount, null, 'FG');
                $detail->save();
            }
        }

        foreach ($list as $RO => $rows) {
            $request_order = RequestOrder::findOrFail($RO);
            $prefix_code = $outgoing_good->customer->code ?? "C$outgoing_good->customer_id";

            $delivery_order = $outgoing_good->delivery_orders()->create([
                'number' => $this->getNextSJDeliveryNumber($outgoing_good->date),
                'indexed_number' => $this->getNextSJDeliveryIndexedNumber($outgoing_good->date, $prefix_code),
                'transaction' =>  $outgoing_good->transaction,
                'customer_id' => $outgoing_good->customer_id,
                'customer_name' => $outgoing_good->customer_name,
                'customer_phone' => $outgoing_good->customer_phone,
                'customer_address' => $outgoing_good->customer_address,
                'customer_note' => $outgoing_good->customer_note,
                'description' => $outgoing_good->description,
                'date' => $outgoing_good->date,
                'vehicle_id' => $outgoing_good->vehicle_id,
                'rit' => $outgoing_good->rit,
            ]);

            foreach ($rows as $DTL => $row) {
                $request_order_item = RequestOrderItem::find($DTL);
                $detail = $delivery_order->delivery_order_items()->create($row);
                $detail->item->transfer($detail, $detail->unit_amount, null, 'FG');

                $detail->request_order_item()->associate($request_order_item);
                $detail->save();
                $request_order_item->calculate();
            }

            $delivery_order->request_order()->associate($request_order);
            $delivery_order->save();
        }
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $outgoing_good = OutgoingGood::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if ($outgoing_good->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        if ($mode == "DELETED" && $outgoing_good->status != 'OPEN') $this->error("The data $outgoing_good->status state, is not allowed to be $mode");

        if ($mode == 'VOID') {
            $outgoing_good->status = "VOID";
            $outgoing_good->save();
        }

        foreach ($outgoing_good->outgoing_good_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        $outgoing_good->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
