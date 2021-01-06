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
                    'packing_items.item',
                    'customer',
                    'shift'
                ])->filter($filter)->latest()->orderBy('id', 'DESC')->get();

                break;

            default:
                $packings = Packing::with([
                    'created_user',
                    // 'packing_items.item'=> function($q) { $q->select(['id', 'code', 'part_number', 'part_name']); },
                    // 'customer'=> function($q) { $q->select(['id', 'code', 'name']); },
                    'packing_items.item',
                    'customer',
                    'shift'
                ])->filter($filter)->latest()->orderBy('id', 'DESC')->collect();

                $packings->getCollection()->transform(function($row) {
                    $row->append(['is_relationship']);
                    return $row;
                });
                break;
        }

        return response()->json($packings);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextPackingNumber()]);

        ## Create the Packing Goods.
        $packing = Packing::create($request->all());

        $row = $request->packing_items;
        ## Packing Items only 1 row detail (relation = $model->hasOne)
        if($row) {
            ## Create the Packing item. Note: with "hasOne" Relation.
            $detail = $packing->packing_items()->create($request->packing_items);

            ## Calculate stock on after the Packing items Created!
            $detail->item->transfer($detail, $detail->unit_amount, 'FG', 'WIP');

            $faults = $row['packing_item_faults'];
            for ($i=0; $i < count($faults); $i++) {
                $fault = $faults[$i];
                if($fault['fault_id'] || $fault['quantity'] ) {
                    ## create fault on the Packing Goods Created!
                    $detail->packing_item_faults()->create($fault);
                }
            }

            ## Calculate "NC" stock on after the Item Faults Created!

            if ($detail->unit_faulty > 0) {
                $detail->item->transfer($detail, $detail->unit_faulty, 'NC', 'WIP');
            }

            $detail->setPackingItemOrder();
        }

        $this->DATABASE::commit();
        return response()->json($packing);
    }

    public function show($id)
    {
        if(request('mode') == 'view') {
            $addWith = [
                'shift',
                'packing_items.packing_item_orders.work_order_item.work_order'
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

        $packing->append(['has_relationship']);

        return response()->json($packing);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $packing = Packing::findOrFail($id);

        if($packing->is_relationship) $this->error('The data has RELATIONSHIP, is not allowed to be updated!');
        if($packing->status != "OPEN") $this->error("The data on $packing->satus state , is not allowed to be updated!");

        $packing->update($request->all());

        $row = $request->packing_items;
        ## Packing Items only 1 row detail (relation = $model->hasOne)
        if($row) {
            $oldDetail = $packing->packing_items->find($row['id']);
            if($oldDetail) {
                ## Calculate stock on before the Packing items updated!
                $oldDetail->item->distransfer($oldDetail);
            }

            ## Update or Create detail row
            $newDetail = $packing->packing_items->updateOrCreate(['id' => $row['id']], $row);
            ## Calculate stock on after the Packing items updated!
            $newDetail->item->transfer($newDetail, $newDetail->unit_amount, 'FG', 'WIP');

            $faults = $row['packing_item_faults'];
            ## Delete fault on the Packing Good updated!
            $packing->packing_items->packing_item_faults()->forceDelete();

            for ($i=0; $i < count($faults); $i++) {
                $fault = $faults[$i];
                if($fault['fault_id'] || $fault['quantity'] ) {
                    ## create fault on the Packing Good updated!
                    $packing->packing_items->packing_item_faults()->create($fault);
                }
            }

            ## Calculate stock on after the NC items updated!
            if ($newDetail->unit_faulty > 0) {
                $newDetail->item->transfer($newDetail, $newDetail->unit_faulty, 'NC', 'WIP');
            }
            $newDetail->setPackingItemOrder();
        }

        // $this->error('LOLOS');

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

        ## Calculate Stok Before deleting
        $detail->item->distransfer($detail);

        ## Delete Packing Item order.
        $detail->packing_item_orders->each(function($item) {
            $work_order_item = $item->work_order_item;
            $item->delete();

            $work_order_item->calculate();
        });

        ## Delete Packing faults.
        $detail->packing_item_faults()->delete();

        $detail->delete();

        $packing->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
