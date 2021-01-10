<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Income\DeliveryTask as Request;
use App\Http\Controllers\ApiController;
// use App\Filters\Income\DeliveryTask as Filters;
use App\Filters\Filter as Filter;
use App\Models\Income\DeliveryTask;
use App\Traits\GenerateNumber;

class DeliveryTasks extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $delivery_tasks = DeliveryTask::filter($filter)->latest()->get();
                break;

            case 'datagrid':
                $delivery_tasks = DeliveryTask::with(['customer'])->filter($filter)->latest()->get();
                // $delivery_tasks->each->append(['is_relationship']);
                break;

            default:
                $delivery_tasks = DeliveryTask::with(['created_user','customer'])->filter($filter)->latest()->collect();
                $delivery_tasks->getCollection()->transform(function($item) {
                    // $item->append(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($delivery_tasks);
    }

    public function outstanding(Filter $filter)
    {
        $delivery_tasks = DeliveryTask::with(['created_user','customer'])->filter($filter)->latest()->collect();
        $delivery_tasks->getCollection()->transform(function($item) {
            // $item->append(['is_relationship']);
            return $item;
        });

        return response()->json($delivery_tasks);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        if(!$request->number) $request->merge(['number'=> $this->getNextDeliveryTaskNumber()]);

        $delivery_task = DeliveryTask::create($request->input());

        $rows = $request->delivery_task_items;
        for ($i=0; $i < count($rows); $i++) {
            // create detail item created!
            $detail = $delivery_task->delivery_task_items()->create($rows[$i]);
        }

        if ($request->has('incoming_good_id')) {
            $delivery_task->incoming_good_id = $request->incoming_good_id;
            $delivery_task->save();
        }

        $delivery_task->setCommentLog("PDO [$delivery_task->fullnumber] has been created!");

        $this->DATABASE::commit();
        return response()->json($delivery_task);
    }

    public function show($id)
    {
        $delivery_task = DeliveryTask::with([
            'customer.customer_trips',
            'delivery_task_items.item.item_units',
            'delivery_task_items.item.unit',
            'delivery_task_items.unit',
        ])->withTrashed()->findOrFail($id);

        $delivery_task->append(['has_relationship']);

        return response()->json($delivery_task);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_task = DeliveryTask::findOrFail($id);

        if ($delivery_task->status == 'CLOSED') {
            $this->error('DELIVERY (TASK) has been CLOSED state, is not allowed to be changed!');
        }

        if ($delivery_task->trashed()) {
            $this->error('DELIVERY (TASK) has trashed, is not allowed to be changed!');
        }

        $delivery_task->update($request->input());

        foreach ($delivery_task->delivery_task_items as $detail) {
            if (!collect($request->delivery_task_items)->contains('id', $detail->id)) {
                ## FORCE DELETE DETAIL HAS REMOVED.
                $detail->forceDelete();
            }
        }

        $rows = $request->delivery_task_items;
        for ($i=0; $i < count($rows); $i++) {
            ## CREATE OR UPDATE INPUT DETAIL
            $detail = $delivery_task->delivery_task_items()->updateOrCreate(['id' => $rows[$i]['id'] ?? null], $rows[$i]);
            if ($detail->validateDetailUpdate() == false) {
                $request->validate(
                    ["delivery_task_items.$i.quantity" => "not_in:". $rows[$i]['quantity'] ],
                    ["delivery_task_items.$i.quantity.not_in" => "Summary Verified has over " ]
                );
            }
        }

        $delivery_task->setCommentLog("PDO [$delivery_task->fullnumber] has been updated!");

        $this->DATABASE::commit();
        return response()->json($delivery_task);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $delivery_task = DeliveryTask::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');

        if ($mode == "VOID") {
            if ($delivery_task->status == 'VOID') $this->error("DELIVERY (TASK) $delivery_task->status state, is not allowed to be $mode");
        }
        else {
            if ($delivery_task->status != 'OPEN') $this->error("DELIVERY (TASK) $delivery_task->status state, is not allowed to be $mode");
        }

        if ($delivery_task->is_overtime) $this->error("DELIVERY (TASK) has overtime, is not allowed to be $mode");

        if($mode == "VOID") {
            $delivery_task->status = "VOID";
            $delivery_task->save();
        }

        foreach ($delivery_task->delivery_task_items as $detail) {
            if ($detail->validateDetailDestroy() == false) {
                $this->error($detail->item->part_name ." has verified, is not allowed to be $mode");
            }
            $detail->delete();
        }

        $delivery_task->delete();

        $action = ($mode == "VOID") ? 'voided' : 'deleted';
        $delivery_task->setCommentLog("PDO [$delivery_task->fullnumber] has been $action !");

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }
}
