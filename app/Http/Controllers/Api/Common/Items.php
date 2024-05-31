<?php

namespace App\Http\Controllers\Api\Common;

use App\Filters\Common\Item as Filter;
use App\Http\Requests\Common\Item as Request;
use App\Http\Controllers\ApiController;
use App\Http\Resources\Common\ItemResource;
use App\Models\Common\Item;
use App\Models\Common\ItemStockable;
use App\Models\Income\AccInvoice;
use App\Models\Income\DeliveryOrderItem;
use App\Models\Income\RequestOrder;
use App\Models\Warehouse\IncomingGoodItem;

class Items extends ApiController
{
    public function index(Filter $filter)
    {

        $collection = function ($item) {
            if (request()->has('delivery_date')) {
                $date = request('delivery_date');
                if (request()->has('amount_delivery_to_load')) {
                    $item->amount_delivery = (double) ($item->amount_delivery_verify($date) - $item->amount_delivery_load($date));
                }
                else if (request()->has('amount_delivery_to_verify')) {
                    $item->amount_delivery = (double) ( $item->amount_delivery_task($date) - $item->amount_delivery_verify($date));
                }
                else {
                    $item->amount_delivery = [
                        "FG" => $item->totals["FG"],
                        "VERIFY" => $item->amount_delivery_verify($date),
                        "TASK.REG" => $item->amount_delivery_task($date, 'REGULER'),
                        "TASK.RET" => $item->amount_delivery_task($date, 'RETURN'),
                        "LOAD.REG" => $item->amount_delivery_load($date, 'REGULER'),
                        "LOAD.RET" => $item->amount_delivery_load($date, 'RETURN')
                    ];
                }
            }
            if (request('appends')) {
                $item->append(explode(',', request('appends')));
            }
            return $item;
        };

        switch (request('mode')) {
          case 'resource':
            $items = Item::filter($filter)->get();
            return ItemResource::collection($items);

          case 'all':
            $items = Item::with(['item_prelines','item_units','unit'])->filter($filter)->get()->map($collection);
          break;

          case 'datagrid':
            $items = Item::with(['item_prelines','item_units', 'brand', 'customer', 'specification'])->filter($filter)->latest()->get();
          break;

          case 'itemstock':
            $items = Item::filter($filter)->get(['id'])->map->append('totals');
          break;

          case 'stockables':
          $items = ItemStockable::whereHas('item', function ($q) use ($filter) {
            return $q->filter($filter);
          })->get();

          break;

          default:
            $items = Item::with(['item_prelines','item_units', 'unit', 'brand', 'customer', 'specification'])
                ->filter($filter)->collect();

            $items->getCollection()->transform($collection);
          break;
        }

        return response()->json($items);
    }

    public function invoice_cards()
    {
        $stockards = collect();

        if (!$invoice = AccInvoice::find(request('invoice_id'))) return $this->error('Filter "INVOICE" is required!');
        if (!$item = Item::find(request('item_id'))) return $this->error('Filter "PART" is required!');
        $request_order = request('request_order_id') ? RequestOrder::findOrFail(request('request_order_id')) : null;

        $incoming_good_items = IncomingGoodItem::where('item_id', $item->id)
        ->whereHas('request_order_item', function($q) use ($invoice, $request_order) {
            return $q
            ->when($request_order,
                function($q) use ($request_order) {
                    return $q->where('request_order_id', $request_order->id);
                },
                function($q) use ($invoice) {
                    $q->when($invoice->request_orders->count(),
                        function($q) use ($invoice) {
                            return $q->whereIn('request_order_id', $invoice->request_orders->pluck('id'));
                        },
                        function($q) use ($invoice) {
                            return $q->whereHas('delivery_order_items', function($q) use ($invoice) {
                                return $q->whereHas('delivery_order', function($q) use ($invoice) {
                                return $q->where('acc_invoice_id', $invoice->id);
                                });
                            });
                        }
                    );
            });
          })
          ->oldest()->get()->map(function ($item) {
            $item['date'] = $item->incoming_good->date;
            $item['number'] = $item->incoming_good->fullnumber;
            $item['indexed_number'] = $item->incoming_good->indexed_number;
            $item['unit'] = $item->unit;
            $item['status'] = $item->status;

            return $item;
        });

        $delivery_order_items = DeliveryOrderItem::where('item_id', $item->id)
          ->whereHas('delivery_order', function($q) use ($invoice, $request_order) {
            return $q
            ->when($request_order, function($q) use ($request_order) {
                $q->where('request_order_id', $request_order->id);
            })
            ->where('acc_invoice_id', $invoice->id);
          })
          ->oldest()->get()->map(function ($item) {
            $item['date'] = $item->delivery_order->date;
            $item['number'] = $item->delivery_order->fullnumber;
            $item['indexed_number'] = $item->delivery_order->indexed_number;
            $item['unit'] = $item->unit;
            $item['status'] = $item->status;

            return $item;
        });

        $stockards = $stockards
            ->merge($incoming_good_items)
            ->merge($delivery_order_items)
            ->map(function($item) {
                $item['union_key'] = $item->getTable() ."-". $item['id'];
                $item['quantity_in'] = $item->getTable() == 'incoming_good_items' ? $item['unit_amount'] : 0;
                $item['quantity_out'] = $item->getTable() != 'incoming_good_items' ? $item['unit_amount'] : 0;

                return $item;
            });

        $rows = $stockards
            ->sortBy('date')
            ->values();


        return response()->json([
            'summary_incoming' => $stockards->sum('quantity_in'),
            'summary_outgoing' => $stockards->sum('quantity_out'),
            'data' => $rows,
            'count' => $stockards->count()
        ]);
    }

    public function delivery_cards()
    {
        $stockards = collect();
        $date = request('date');

        if (!$date) return $this->error('Filter "Date" is required!');
        if (!$item = Item::find(request('item_id'))) return $this->error('Filter "PART" is required!');

        $incoming_good_items = IncomingGoodItem::where('item_id', $item->id)
          ->whereHas('incoming_good', function($q) {
            $date = explode(',', request('date'));
            return $q->whereBetween('date', $date)->where('status', 'VALIDATED');
          })
          ->oldest()->get()->map(function ($item) {
            $item['date'] = $item->incoming_good->date;
            $item['number'] = $item->incoming_good->fullnumber;
            $item['unit'] = $item->unit;
            $item['status'] = $item->status;

            return $item;
        });

        $delivery_order_items = DeliveryOrderItem::where('item_id', $item->id)
          ->whereHas('delivery_order', function($q) {
            $date = explode(',', request('date'));
            return $q->whereBetween('date', $date);
          })
          ->oldest()->get()->map(function ($item) {
            $item['date'] = $item->delivery_order->date;
            $item['number'] = $item->delivery_order->fullnumber;
            $item['unit'] = $item->unit;
            $item['status'] = $item->status;

            return $item;
        });

        $stockards = $stockards
            ->merge($incoming_good_items)
            ->merge($delivery_order_items)
            ->map(function($item) {
                $item['union_key'] = $item->getTable() ."-". $item['id'];
                $item['quantity_in'] = $item->getTable() == 'incoming_good_items' ? $item['unit_amount'] : 0;
                $item['quantity_out'] = $item->getTable() != 'incoming_good_items' ? $item['unit_amount'] : 0;

                return $item;
            });

        $rows = $stockards
            ->sortBy('date')
            ->skip(request('skip', 0))
            ->take(request('take', 20))
            ->values();

        $sum_incoming_good = (double) IncomingGoodItem::where('item_id', $item->id)
          ->whereHas('incoming_good', function($q) {
            $date = explode(',', request('date'));
            return $q->where('date', '>=', $date[0])->where('status', 'VALIDATED');
          })
          ->sum(app('db')->raw('quantity * unit_rate'));


        $sum_delivery_order = (double) DeliveryOrderItem::where('item_id', $item->id)
          ->whereHas('delivery_order', function($q) {
            $date = explode(',', request('date'));
            return $q->where('date', '>=', $date[0]);
          })
          ->sum(app('db')->raw('quantity * unit_rate'));

        $e_delivery_order = DeliveryOrderItem::where('item_id', $item->id)
          ->whereHas('delivery_order', function($q) {
            $date = explode(',', request('date'));
            return $q->where('date', '>=', $date[0]);
          })->get();

        $s_delivery_order = DeliveryOrderItem::where('item_id', $item->id)
            ->whereHas('delivery_order', function($q) {
                $date = explode(',', request('date'));
                return $q->whereBetween('date', $date);
            })->get();

        $awal = ($item->totals['*'] - ($sum_incoming_good - $sum_delivery_order));

        return response()->json([
            'data' => $rows,
            'count' => $stockards->count(),
            'summary_incoming' => $stockards->sum('quantity_in'),
            'summary_outgoing' => $stockards->sum('quantity_out'),
            'begining' => $awal,
            'stock' => $item->totals['*'],
        ]);
    }

    public function stockables(Filter $filter)
    {
        switch (request('mode')) {
          case 'all':
            $items = ItemStockable::whereHas('item', function ($q) use ($filter) {
                return $q->filter($filter);
            })->get();
          break;

          default:
            $items = ItemStockable::with('item.unit')->whereHas('item', function ($q) use ($filter) {
                return $q->filter($filter);
            })->latest()->collect();

            $items->getCollection()->transform(function($item) {
                $item->append(['base_data']);
                return $item;
            });

          break;
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!strlen($request->code)) {
            $code = Item::withSampled()->select('id')->max('id');
            $code = str_pad($code + 1, 6, '0', STR_PAD_LEFT);
            $request->merge(['code' => $code]);
        }

        $item = Item::create($request->all());

        $preline_rows = $request->item_prelines ?? [];
        for ($i=0; $i < count($preline_rows); $i++) {
            // create pre production on the item updated!
            if ($i == 0) $preline_rows[$i]["ismain"] = 1;
            if ($preline_rows[$i]['line_id']) $item->item_prelines()->create($preline_rows[$i]);
        }

        $unit_rows = $request->item_units ?? [];
        for ($i=0; $i < count($unit_rows); $i++) {
            // create item units on the item updated!
            if ($unit_rows[$i]['unit_id']) $item->item_units()->create($unit_rows[$i]);
        }

        if(!$item->code) $item->update(['code' => $item->id]);

        $this->sampleTimestamp($item, $request);

        $this->DATABASE::commit();
        return response()->json($item);
    }

    public function show($id)
    {
        $this->DATABASE::beginTransaction();
        $with = [
            'customer','brand','category_item', 'type_item', 'size', 'unit',
            'category_item_price'
        ];
        $item = Item::withSampled()->with(array_merge($with, ['item_prelines.line', 'item_units']))->findOrFail($id);
        $item->is_editable = (!$item->is_related);

        $this->DATABASE::commit();
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $item = Item::withSampled()->findOrFail($id);

        $item->update($request->input());

        // Delete pre production on the item updated!
        $item->item_prelines()->delete();
        $preline_rows = $request->item_prelines;
        for ($i=0; $i < count($preline_rows); $i++) {
            // create pre production on the item updated!
            if($i == 0) $preline_rows[$i]["ismain"] = 1;
            $item->item_prelines()->create($preline_rows[$i]);
        }

        // Delete item units on the item updated!
        $item->item_units()->delete();
        $unit_rows = $request->item_units;
        for ($i=0; $i < count($unit_rows); $i++) {
            // create item units on the item updated!
            $item->item_units()->create($unit_rows[$i]);
        }

        $this->sampleTimestamp($item, $request);

        $this->DATABASE::commit();
        return response()->json($item);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $item = Item::withSampled()->findOrFail($id);

        if ($item->is_relationship) $this->error("CODE:$item->code has data relation, Delete not allowed!");

        $item->item_prelines()->delete();
        $item->item_units()->delete();
        $item->delete();

        $this->DATABASE::commit();
        return response()->json(array_merge($item->toArray(), ['success' => true]));
    }

    public function sampleTimestamp($item, $request)
    {
        if ($item->sample && $request->sample_priced_at === true) {
            $item->sample_priced_at = now();
            $item->save();
        }

        if ($item->sample && $request->sample_enginered_at === true) {
            $item->sample_enginered_at = now();
            $item->sample_enginered_by = auth()->user()->id;
            $item->save();
        }

        if ($item->sample && $request->sample_depicted_at === true) {

            if ($request->depicts == null || collect($request->depicts)->count() == 0)
            {
                $this->error('Drawing not available!');
            }

            $item->depicts = $request->depicts;
            $item->sample_depicted_at = now();
            $item->save();
        }
    }

    public function sampleValidation($id)
    {
        $item = Item::withSampled()->findOrFail($id);

        ## PRIMARY REQUIRED! ##
        if (!$item->sample) return $this->error("Part [$item->code] $item->part_name is not sample!");
        if (!$item->sample_enginered_at) return $this->error("Part [$item->code] $item->part_name has not enginered!");
        if (!$item->sample_priced_at) return $this->error("Part [$item->code] $item->part_name has not Priced!");
        if ($item->sample_validated_at) return $this->error("Part [$item->code] $item->part_name has been validated!");

        ## ADDITIONAL REQUIRED! ##
        if (!$item->item_prelines->count()) return $this->error("Part [$item->code] $item->part_name has not prelines!");
        if (!$item->unit) return $this->error("Part [$item->code] $item->part_name has not unit!");
        if (!$item->specification) return $this->error("Part [$item->code] $item->part_name has not specification!");

        $item->sample_validated_by = auth()->user()->id;
        $item->sample_validated_at = now();
        $item->sample = 0;

        $item->save();

        return response()->json($item);
    }
}
