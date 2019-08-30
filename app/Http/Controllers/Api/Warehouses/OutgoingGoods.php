<?php
namespace App\Http\Controllers\Api\Warehouses;

use App\Filters\Warehouse\OutgoingGood as Filters;
use App\Http\Requests\Warehouse\OutgoingGood as Request;
use App\Http\Controllers\ApiController;
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
                $outgoing_goods = OutgoingGood::with(['customer','operator'])
                  ->filter($filters)
                  ->latest()->get();
                $outgoing_goods->each->setAppends(['is_relationship']);
                break;

            default:
                $outgoing_goods = OutgoingGood::with(['operator',
                    'delivery_orders' => function($q) { $q->select(['id', 'outgoing_good_id', 'number', 'numrev']);},
                    'customer' => function($q) { $q->select(['id', 'name']);}
                ])->filter($filters)
                  ->latest()->collect();

                $outgoing_goods->getCollection()->transform(function($item) {
                    $item->setAppends(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($outgoing_goods);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextOutgoingGoodNumber()]);

        $outgoing_good = OutgoingGood::create($request->all());

        $rows = $request->outgoing_good_items;

        if(count($rows) <= 0) abort(501, 'Part detail not found!');
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // create item row on the incoming Goods updated!
            $detail = $outgoing_good->outgoing_good_items()->create($row);

            $TransDO = $outgoing_good->transaction == "RETURN" ? 'PDO.RET' : 'PDO.REG';
            $detail->item->transfer($detail, $detail->unit_amount, null, $TransDO);
            $detail->item->transfer($detail, $detail->unit_amount, null, 'VDO');
        }

        $this->storeRequestOrder($outgoing_good);
        $this->storeDeliveryOrder($outgoing_good);

        OutgoingGoodVerification::wait()
          ->whereHas('item', function($query) use($outgoing_good) {
              return $query->where('customer_id', $outgoing_good->customer_id);
          })
          ->update(['outgoing_good_id' => $outgoing_good->id]);

        // $this->error('LOLOS => '.$request->number .' >> '. $request->transaction);


        $this->DATABASE::commit();
        return response()->json($outgoing_good);
    }

    public function show($id)
    {
        $outgoing_good = OutgoingGood::with([
            'customer',
            'outgoing_good_items.item.item_units',
            'outgoing_good_items.unit'
        ])->withTrashed()->findOrFail($id);

        // dd($outgoing_good->request_order_item_closed);

        $outgoing_good->setAppends(['has_relationship']);

        return response()->json($outgoing_good);
    }

    public function storeRequestOrder($outgoing_good)
    {
        if ($outgoing_good->transaction != "RETURN" && $outgoing_good->customer->order_mode == "ACCUMULATE") {

            $model = RequestOrder::whereMonth('date', substr($outgoing_good->date,3,2))
                ->where('customer_id', $outgoing_good->customer_id)
                ->where('order_mode', $outgoing_good->customer->order_mode)
                ->latest()->first();

            if(!$model) {
                $model = new RequestOrder;
                $begin = \Carbon::parse($outgoing_good->date)->startOfMonth()->format('Y-m-d');
                $until = \Carbon::parse($outgoing_good->date)->endOfMonth()->format('Y-m-d');
                $order_mode = $outgoing_good->customer->order_mode;

                $model->date  = $outgoing_good->date;
                $model->customer_id = $outgoing_good->customer_id;
                $model->order_mode   = $order_mode;
                $model->order_mode   = 'REGULER';
                $model->description   = "ACCUMULATE P/O. $begin - $until";
                // For model update
                if(!$model->id) {
                    $model->number = $this->getNextRequestOrderNumber($outgoing_good->date);
                }
                $model->save();

                $outgoing_good->request_order_id = $model->id;
                $outgoing_good->save();
            }

            $rows = $outgoing_good->outgoing_good_items
                ->groupBy('item_id')
                ->map(function($group) {
                    return [
                        'item_id' => $group[0]->item_id,
                        'unit_id' => $group[0]->item->unit_id,
                        'unit_rate' => 1,
                        'quantity' => $group->sum('unit_amount'),
                        'price' => 0,
                    ];
                });

            foreach ($rows as $row) {
                $detail = $model->request_order_items()->create($row);
                $detail->outgoing_good_id = $outgoing_good->id;
                $detail->save();
            }
        }
    }

    public function storeDeliveryOrder($outgoing_good) {

        $list = [];
        $request_order_items = RequestOrderItem::whereHas('request_order', function($q) use($outgoing_good) {
            return $q->where('transaction', $outgoing_good->transaction)
              ->where('customer_id', $outgoing_good->customer_id);
          })->get()->filter(function($x) {
            return ($x->unit_amount > $x->total_delivery_order_item);
          })->map(function($detail) {
            $detail->sort_date = $detail->request_order->date;
            return $detail;
          })->sortBy('sort_date');

        $rows = $outgoing_good->outgoing_good_items->groupBy('item_id')->map(function($group, $key){
            return $group->sum('unit_amount');
        });

        foreach ($request_order_items as $detail) {
            if(isset($rows[$detail->item_id])) {

                $max_amount = $rows[$detail->item_id];
                $unit_amount = $detail->unit_amount - $detail->total_delivery_order_item;
                $unit_amount = ($max_amount < $unit_amount ? $max_amount : $unit_amount);

                $rows[$detail->item_id] -= $unit_amount;

                if($unit_amount > 0 ){
                    $RO = $detail->request_order_id;
                    $DTL = $detail->id;

                    $list[$RO][$DTL] = [
                        'item_id' => $detail->item_id,
                        'unit_id' => $detail->item->unit_id,
                        'unit_rate' => 1,
                        'quantity' => $unit_amount
                    ];
                }
            }
        }

        foreach ($list as $RO => $rows) {
            $delivery_order = $outgoing_good->delivery_orders()->create([
                'number' => $this->getNextSJDeliveryNumber(),
                'transaction' => 'REGULER',
                'customer_id' => $outgoing_good->customer_id,
                'customer_name' => $outgoing_good->customer_name,
                'customer_phone' => $outgoing_good->customer_phone,
                'customer_address' => $outgoing_good->customer_address,

                'operator_id' => $outgoing_good->operator_id,
                'date' => $outgoing_good->date,
                'time' => $outgoing_good->time,
                'due_date' => $outgoing_good->due_date,
                'due_time' => $outgoing_good->due_time,
            ]);

            foreach ($rows as $DTL => $row) {

                $detail = $delivery_order->delivery_order_items()->create($row);
                $detail->item->transfer($detail, $detail->unit_amount, null, 'FG');

                $detail->request_order_item_id = $DTL;
                $detail->save();
            }

            $delivery_order->request_order_id = $RO;
            $delivery_order->save();
        }
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $outgoing_good = OutgoingGood::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($outgoing_good->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        if($mode == "DELETED" && $outgoing_good->status != 'OPEN') $this->error("The data $outgoing_good->status state, is not allowed to be $mode");

        if($mode == 'VOID') {
            $outgoing_good->status = "VOID";
            $outgoing_good->save();
        }

        foreach ($outgoing_good->request_order_items as $detail) {

            if($detail->request_order->status != "OPEN") $this->error("The data has RELATIONSHIP [#". $detail->request_order->status ."], is not allowed to be $mode");
            $detail->forceDelete();

            $request_order = RequestOrder::find($detail->request_order_id);
            if($request_order->request_order_items->count() == 0) {
                $request_order->status = 'VOID';
                $request_order->save();
                $request_order->delete();
            }
        }

        // ????? SCHEMA 1: REMOVE DETAIL & CALCULATE BACK ITEMSTOCK
        foreach ($outgoing_good->outgoing_good_items as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        $outgoing_good->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
