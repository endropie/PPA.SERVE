<?php
namespace App\Http\Controllers\Api\Factories;

use App\Filters\Factory\Packing as Filters;
use App\Http\Requests\Factory\Packing as Request;
use App\Http\Controllers\ApiController;
use App\Models\Factory\Packing;
use App\Models\Factory\WorkOrder;
use App\Models\Factory\WorkOrderItem;
use App\Traits\GenerateNumber;

class Packings extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filter)
    {
        switch (request('mode')) {
            case 'all':
                $packings = Packing::filter($filter)->get();
                break;

            case 'datagrid':
                $packings = Packing::with([
                    'packing_items',
                    'packing_items.item'=> function($q) { $q->select(['id', 'code', 'part_number', 'part_name']); },
                    'customer'=> function($q) { $q->select(['id', 'code', 'name']); },
                    'shift'
                ])->filter($filter)->latest()->orderBy('id', 'DESC')->get();

                break;

            default:
                $packings = Packing::with([
                    'packing_items',
                    'packing_items.item'=> function($q) { $q->select(['id', 'code', 'part_number', 'part_name']); },
                    'customer'=> function($q) { $q->select(['id', 'code', 'name']); },
                    'shift'
                ])->filter($filter)->latest()->orderBy('id', 'DESC')->collect();

                $packings->getCollection()->transform(function($row) {
                    $row->setAppends(['is_relationship']);

                    $row->packing_items->work_order_number = (
                      $row->packing_items->work_order_item
                        ?  $row->packing_items->work_order_item->work_order->number : null
                    );
                    return $row;
                });
                break;
        }

        return response()->json($packings);
    }

    public function multistore(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextPackingNumber()]);

        $totals = (double) $request->input('packing_items.quantity') * $request->input('packing_items.unit_rate');

        $faults = $request->input('packing_items.type_fault_id', false)
            ? collect($request->input('packing_items.packing_item_faults', []))
            : collect([]);

        $partials = collect([]);

        $work_order_items = WorkOrderItem::where('item_id', $request->input('packing_items.item_id'))
            ->whereRaw('amount_process > amount_packing')
            ->whereHas('work_order', function($q) {
              return $q->stateHasNot('PACKED');
            })
            ->orderBy('id')
            ->get();

        foreach ($work_order_items as $detail) {
            if ($detail->item_id !== $request->input('packing_items.item_id')) continue;

            $new = ['id' => $detail->id, 'quantity' => 0, 'total' => 0];
            $detail->available = $detail->amount_process - $detail->amount_packing;
            if ( $totals > 0 && $detail->available > 0 && $detail->item_id == request('packing_items.item_id'))
            {
                $amount = $detail->available > $totals ? $totals : $detail->available;
                $new = array_merge($new, ['quantity' => $amount, 'total' => $amount]);
                $totals -= $amount;
                $detail->available -= $amount;
            }
            if ( $totals <= 0 && $detail->available > 0 && $faults->sum('quantity') > 0)
            {
                $new = array_merge($new, ['faults' => array()]);
                foreach ($faults as $key => $fault) {
                    if($detail->available <= 0) break;
                    if ( $detail->available > 0) {
                        $amount = $detail->available > $fault['quantity'] ? $fault['quantity'] : $detail->available;
                        $new['faults'][] = ['fault_id' => $fault['fault_id'], 'quantity' => $amount];
                        $new['total'] = (double) $new['total'] + $amount;
                    }

                    $faults->transform(function($item, $itemkey) use($key, $amount) {
                        if ($key == $itemkey) $item['quantity'] -= $amount;
                        return $item;
                    });

                    $detail->available -= $amount;
                }
            }
            if (!empty($new) && (double) $new['total'] > 0) {
                $partials->push($new);
            }
        }

        if ($partials->count() <= 0) $this->error("WORK ORDER Not Available. Not allowed to be Created!");

        $packings = collect([]);
        foreach ($partials as $key => $partial) {

            if ($partial['total'] <= 0) continue;

            // Create the Packing Goods.
            $packing = Packing::create($request->all());

            $row = $request->packing_items;
            // Packing Items only 1 row detail (relation = $model->hasOne)
            if($row) {
                // Create the Packing item. Note: with "hasOne" Relation.
                $row = array_merge($request->packing_items, [
                    'quantity' => ($partial['quantity'] / ($row['unit_rate'] ?? 1)),
                    'work_order_item_id' => $partial['id'],
                ]);

                $detail = $packing->packing_items()->create($row);

                // Calculate stock on after the Packing items Created!
                $detail->item->transfer($detail, $detail->unit_amount, 'FG', 'WIP');

                if (count($partial['faults'] ?? []) > 0)
                {
                    $faults = $partial['faults'];
                    for ($i=0; $i < count($faults); $i++) {
                        $fault = $faults[$i];
                        if($fault['fault_id'] || $fault['quantity'] ) {
                            // create fault on the Packing Goods Created!
                            $detail->packing_item_faults()->create($fault);
                        }
                    }
                }

                // Calculate "NG" stock on after the Item Faults Created!
                $NG = (double) $detail->packing_item_faults()->sum('quantity');
                if ($NG > 0) {
                    $detail->item->transfer($detail, $NG, 'NG', 'WIP');
                }
                $detail->amount_faulty = $NG * $detail->unit_rate;
                $detail->save();

                $detail->work_order_item->calculate();
            }

            $packings->push($packing);
        }

        $this->DATABASE::commit();
        return response()->json($packings);
    }

    public function store(Request $request)
    {
        if (strtoupper(request('mode')) ?? 'MULTICREATE') {
            return $this->multistore($request);
        }

        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextPackingNumber()]);

        // Create the Packing Goods.
        $packing = Packing::create($request->all());

        $row = $request->packing_items;
        // Packing Items only 1 row detail (relation = $model->hasOne)
        if($row) {
            // Create the Packing item. Note: with "hasOne" Relation.
            $detail = $packing->packing_items()->create($request->packing_items);

            // Calculate stock on after the Packing items Created!
            $detail->item->transfer($detail, $detail->unit_amount, 'FG', 'WIP');

            $faults = $row['packing_item_faults'];
            for ($i=0; $i < count($faults); $i++) {
                $fault = $faults[$i];
                if($fault['fault_id'] || $fault['quantity'] ) {
                    // create fault on the Packing Goods Created!
                    $detail->packing_item_faults()->create($fault);
                }
            }

            // Calculate "NG" stock on after the Item Faults Created!
            $NG = (double) $detail->packing_item_faults()->sum('quantity');
            if ($NG > 0) {
                $detail->item->transfer($detail, $NG, 'NG', 'WIP');
            }
            $detail->amount_faulty = $NG * $detail->unit_rate;
            $detail->save();

            $detail->work_order_item->calculate();
        }

        $this->DATABASE::commit();
        return response()->json($packing);
    }

    public function show($id)
    {
        if(request('mode') == 'view') {
            $addWith = [
                'shift',
                'packing_items.work_order_item.work_order'
            ];
        }
        else $addWith = [];

        $packing = Packing::with(array_merge([
            'customer',
            'operator',
            'packing_items.item.item_units',
            'packing_items.unit',
            'packing_items.packing_item_faults.fault'
        ], $addWith))->withTrashed()->findOrFail($id);

        $packing->setAppends(['has_relationship']);

        return response()->json($packing);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $packing = Packing::findOrFail($id);

        if($packing->is_relationship) $this->error('The data has RELATIONSHIP, is not allowed to be updated!');
        if($packing->status != "OPEN") $this->error("The data on $packing->satus state , is not allowed to be updated!");

        $packing->update($request->input());

        $row = $request->packing_items;
        // Packing Items only 1 row detail (relation = $model->hasOne)
        if($row) {
            $oldDetail = $packing->packing_items->find($row['id']);
            if($oldDetail) {
                // Calculate stock on before the Packing items updated!
                $oldDetail->item->distransfer($oldDetail);
            }

            // Update or Create detail row
            $newDetail = $packing->packing_items->updateOrCreate(['id' => $row['id']], $row);
            // Calculate stock on after the Packing items updated!
            $newDetail->item->transfer($newDetail, $newDetail->unit_amount, 'FG', 'WIP');

            $faults = $row['packing_item_faults'];
            // Delete fault on the Packing Good updated!
            $packing->packing_items->packing_item_faults()->forceDelete();

            for ($i=0; $i < count($faults); $i++) {
                $fault = $faults[$i];
                if($fault['fault_id'] || $fault['quantity'] ) {
                    // create fault on the Packing Good updated!
                    $packing->packing_items->packing_item_faults()->create($fault);
                }
            }

            // Calculate stock on after the NG items updated!
            $NG = (double) $packing->packing_items->packing_item_faults()->sum('quantity') * $newDetail->unit_rate;
            if ($NG > 0) $newDetail->item->transfer($newDetail, $NG, 'NG', 'WIP');

            $newDetail->amount_faulty = $NG ;
            $newDetail->save();

            $newDetail->work_order_item->calculate();
        }

        // $this->error('LOLOS!');

        $this->DATABASE::commit();
        return response()->json($packing);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();
        $packing = Packing::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($packing->is_relationship) $this->error("[$packing->number] has RELATIONSHIP, is not allowed to be $mode!");
        if($mode == "DELETED" && $packing->status != "OPEN") $this->error("[$packing->number] $packing->status state, is not allowed to be $mode!");


        $packing->status = $mode;
        $packing->save();

        $detail = $packing->packing_items;
        $work_order_item = $detail->work_order_item;

        // Calculate Stok Before deleting
        $detail->item->distransfer($detail);

        // Delete Packing.
        $detail->packing_item_faults()->delete();

        $detail->delete();
        $work_order_item->calculate();

        $packing->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
