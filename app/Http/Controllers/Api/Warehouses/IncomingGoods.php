<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\IncomingGood as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Warehouse\IncomingGood as Filter;
use App\Filters\Warehouse\IncomingGoodItem as FilterItem;
use App\Http\Requests\Request as BaseRequest;
use App\Models\Income\Customer;
use App\Models\Warehouse\IncomingGood;
use App\Models\Income\RequestOrder;
use App\Models\Income\RequestOrderItem;
use App\Models\Warehouse\IncomingGoodItem;
use App\Models\Warehouse\IncomingValidation;
use App\Traits\GenerateNumber;

class IncomingGoods extends ApiController
{
    use GenerateNumber;

    public function index(Filter $filter, FilterItem $filterItem)
    {
        switch (request('mode')) {
            case 'all':
                $incoming_goods = IncomingGood::filter($filter)->get();
                break;

            case 'datagrid':
                $incoming_goods = IncomingGood::with(['customer'])->filter($filter)->latest()->get();
                $incoming_goods->each->append(['is_relationship']);
                break;

            default:
                $incoming_goods = IncomingGood::with(['created_user','customer'])->filter($filter)->latest()->collect();
                $incoming_goods->getCollection()->transform(function($item) {
                    $item->append(['is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($incoming_goods);
    }

    public function items(FilterItem $filter_item)
    {
        switch (request('mode')) {
            case 'all':
            $incoming_good_items = IncomingGoodItem::filter($filter_item)->latest()->get();
            break;

            default:
                $incoming_good_items = IncomingGoodItem::with(['unit', 'item', 'incoming_good'])
                  ->filter($filter_item)
                  ->latest()->collect();

                $incoming_good_items->getCollection()->transform(function($row) {
                    return $row;
                });

                break;
        }

        return response()->json($incoming_good_items);
    }

    public function validations($incomingGoodId)
    {
        $validations = IncomingValidation::with(['incoming_validation_items'])
            ->where('incoming_good_id', $incomingGoodId)
            ->latest()->get();

        return response()->json($validations);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        if (!$request->number) $request->merge([
            'number'=> $this->getNextIncomingGoodNumber($request->input('date'))
        ]);

        $prefix = Customer::find($request->input('customer_id'));
        if (!$request->indexed_number && $prefix) {
            $request->merge([
                'indexed_number' => $this->getNextIncomingGoodIndexedNumber($request->input('date'), $prefix->code)
            ]);
        }

        if ($request->reference_number && IncomingGood::where('reference_number', $request->reference_number)->exists()) {
            abort(406, "The number [". $request->reference_number ."] has been used!");
        }

        $incoming_good = IncomingGood::create($request->all());

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $incoming_good->incoming_good_items()->create($row);
            if (!$detail->item->enable) $this->error("PART [". $detail->item->code . "] DISABLED");

        }

        $incoming_good->setCommentLog("Incoming [$incoming_good->fullnumber] has been created!");

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function show($id)
    {
        $incoming_good = IncomingGood::withTrashed()->with([
            'customer',
            'request_order',
            'incoming_good_items.item.item_units',
            'incoming_good_items.unit'
        ])->findOrFail($id);

        $incoming_good->append(['is_relationship','has_relationship']);

        return response()->json($incoming_good);
    }

    public function update(Request $request, $id)
    {

        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        if ($incoming_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');
        if ($incoming_good->is_relationship) $this->error('The data has relationships, is not allowed to be changed');

        $incoming_good->update($request->input());

        // Before Update Force delete incoming goods items
        $incoming_good->incoming_good_items()->forceDelete();

        // Update incoming goods items
        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Update or Create detail row
            $detail = $incoming_good->incoming_good_items()->create($row);
            if (!$detail->item->enable) $this->error("PART [". $detail->item->code . "] DISABLED");
        }

        $incoming_good->setCommentLog("Incoming [$incoming_good->fullnumber] has been updated!");

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($incoming_good->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be $mode");
        if($mode == "DELETED" && $incoming_good->status != 'OPEN') $this->error("The data $incoming_good->status state, is not allowed to be $mode");

        if($mode == 'VOID') {
            $incoming_good->status = "VOID";
            $incoming_good->save();
        }

        ## Delate all validation & detail
        foreach ($incoming_good->incoming_validations as $incoming_validation) {
            $incoming_validation->incoming_validation_items()->delete();
            $incoming_validation->delete();
        }

        ## Delete all incoming & detail
        foreach ($incoming_good->incoming_good_items as $detail) {

            $to = $incoming_good->transaction == 'RETURN' ? 'NCR' : 'FM';

            if (round($detail->item->getTotalStockist($to)) < round($detail->unit_valid)) {
                $name = $detail->item->part_name ." - ". $detail->item->part_subname;
                $this->error("Unit Quantity valid [$name] has Failed to [$mode]");
            }
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        ## Delete request order & detail
        if($request_order = $incoming_good->request_order)
        {
            $request_order->request_order_items()->delete();
            $request_order->delete();
        }

        $incoming_good->delete();

        $action = ($mode == "VOID") ? 'voided' : 'deleted';
        $incoming_good->setCommentLog("Incoming [$incoming_good->fullnumber] has been $action !");

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function rejection(Request $request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        if ($incoming_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];

            // create item row on the incoming Goods updated!
            $detail = $incoming_good->incoming_good_items()->find($row["id"]);
            $detail->update($row);
        }


        $incoming_good->description = $request->input('description', null);
        $incoming_good->status = 'REJECTED';
        $incoming_good->save();

        $incoming_good->setCommentLog("Incoming [$incoming_good->fullnumber] has been rejected!");

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function restoration(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = IncomingGood::findOrFail($id);
        $details = $revise->incoming_good_items;
        foreach ($details as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        if($request->number) {
            $max = (int) IncomingGood::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number' => ($max + 1)]);
        }

        if(!$request->transaction == 'RETURN') $request->merge(['order_mode'=> 'NONE']);

        $incoming_good = IncomingGood::create($request->all());

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $detail = $incoming_good->incoming_good_items()->create($row);
        }

        $incoming_good->setCommentLog("Incoming [$incoming_good->fullnumber] has been created!\nOn Restoration [$revise->fullnumber]");

        $revise->revise_id = $incoming_good->id;
        $revise->save();
        $revise->delete();

        $incoming_good->setCommentLog("Incoming [$revise->fullnumber] has been restored!");

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function validation(Request $request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        $rows = $request->incoming_good_items ?? [];

        foreach ($rows as $row) {
            $detail = $incoming_good->incoming_good_items()->find($row["id"]);
            $detail->update($row);
        }

        if ($incoming_good->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        foreach ($incoming_good->incoming_good_items as $detail) {
            // Calculate stock on "validation" Incoming Goods!
            if ($incoming_good->transaction != 'SAMPLE') {
                $to = $incoming_good->transaction == 'RETURN' ? 'NCR' : 'FM';
                $detail->item->transfer($detail, $detail->unit_valid, $to);
            }
        }

        if (strtoupper($incoming_good->order_mode) === 'NONE' && strtoupper($incoming_good->transaction) !== 'INTERNAL') {
            $this->storeRequestOrder($incoming_good);
        }

        $incoming_good->status = 'VALIDATED';
        $incoming_good->save();

        $incoming_good->setCommentLog("Incoming [$incoming_good->fullnumber] has been validated!");

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function standardization(BaseRequest $request, $id)
    {
        $request->validate([
            'transaction' => "required|in:REGULER,RETURN",
            'order_mode' => "required",
            'registration' => "required",
            'reference_number' => "required",
            'reference_date' => "required",
        ]);

        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($id);

        if ($incoming_good->transaction !== "INTERNAL") $this->error('The data not "INTERNAL" Transaction');

        if ($incoming_good->status !== "VALIDATED") $this->error('The data not "VALIDATED" state');

        $row = $request->only([
            'transaction', 'order_mode', 'registration', 'reference_number', 'reference_date',
        ]);

        $incoming_good->update($row);

        $incoming_good->standardized_at = now();
        $incoming_good->standardized_by = auth()->user()->id;
        $incoming_good->status = "STANDARDIZED";
        $incoming_good->save();

        if (strtoupper($incoming_good->order_mode) === 'NONE') {
            $this->storeRequestOrder($incoming_good);
        }

        $incoming_good->setCommentLog("Incoming [$incoming_good->fullnumber] has been standardized!");

        $this->DATABASE::commit();

        return response()->json($incoming_good);
    }

    public function revision(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $revise = IncomingGood::findOrFail($id);

        if ($revise->is_relationship) $this->error("Data has RELATIONSHIP, is not allowed to be REVISED");
        if ($revise->incoming_validations->count()) $this->error("Data has Partial validation, is not allowed to be REVISED");

        $details = $revise->incoming_good_items;
        foreach ($details as $detail) {
            $detail->item->distransfer($detail);
            $detail->delete();
        }

        if($request->number) {
            $max = (int) IncomingGood::where('number', $request->number)->max('revise_number');
            $request->merge(['revise_number' => ($max + 1)]);
        }

        if(!$request->transaction == 'RETURN') $request->merge(['order_mode'=> 'NONE']);

        $incoming_good = IncomingGood::create($request->all());

        $request_order = $revise->request_order;

        $rows = $request->incoming_good_items;
        for ($i=0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $row['valid'] = $row['valid'] ?? $row['quantity'];
            $detail = $incoming_good->incoming_good_items()->create($row);

            if (isset($row['request_order_item_id'])) {
                $request_order_item = RequestOrderItem::find($row['request_order_item_id']);
                $detail->request_order_item()->associate($request_order_item);
                $detail->save();
            }

            $to = $incoming_good->transaction == 'RETURN' ? 'NCR' : 'FM';
            $detail->item->transfer($detail, $detail->unit_valid, $to);
        }

        if (strtoupper($incoming_good->order_mode) === 'NONE') {
            $this->reviseRequestOrder($revise, $incoming_good);
        }

        if ($request_order) $incoming_good->request_order()->associate($request_order);
        $incoming_good->status = $revise->status;
        $incoming_good->save();

        $incoming_good->setCommentLog("Incoming [$incoming_good->fullnumber] has been created!\nOn Revision [$revise->fullnumber]");

        $revise->status = 'REVISED';
        $revise->revise_id = $incoming_good->id;
        $revise->save();
        $revise->delete();

        $revise->setCommentLog("Incoming [$revise->fullnumber] has been revised!");

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function storePartialValidation(Request $request, $incomingGoodId)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $incoming_good = IncomingGood::findOrFail($incomingGoodId);

        if (!in_array($incoming_good->status, ["OPEN" , 'PARTIAL-VALIDATED'])) $this->error("INCOMING has $incoming_good->status state, is not allowed to be validation");

        $incoming_validation = $incoming_good->incoming_validations()->create([
            'date' => $request->get('validate_date'),
            'description' => $request->get('validate_description'),
        ]);

        foreach ($request->incoming_good_items as $row) {

            if ($row['validate_quantity'] ?? null)
            {
                $detail = $incoming_validation->incoming_validation_items()->firstOrNew(['id' => null], [
                    'quantity' => $row['validate_quantity'],
                ]);

                $detail->incoming_good_item_id = $row['id'];
                $detail->save();

                $incoming_good_item = $detail->incoming_good_item;
                $incoming_good_item->valid = $incoming_good_item->incoming_validation_items->sum('quantity');
                $incoming_good_item->save();

                if (round($incoming_good_item->valid) > round($incoming_good_item->quantity)) $this->error("DETAIL [". $incoming_good_item->item->part_name ."] is OVER-VALIDATE");
            }
        }

        foreach ($incoming_good->incoming_good_items as $detail)
        {

            $detail->item->distransfer($detail);

            ## CALCULATE STOCK NOT DETAIL SAMPLE
            if ($incoming_good->transaction != 'SAMPLE') {
                $to = $incoming_good->transaction == 'RETURN' ? 'NCR' : 'FM';
                $detail->item->transfer($detail, $detail->unit_valid, $to);
            }
        }

        ## SET INCOMING STATUS
        $balance = (double) $incoming_good->incoming_good_items->reduce(function ($total, $item) {
            return $total + (round($item->quantity) -  round($item->valid));
        });

        $incoming_good->status = $balance > 0 ? 'PARTIAL-VALIDATED' : 'VALIDATED';
        $incoming_good->save();

        ## REQUEST ORDER GENERATE ONLY FOR MODE "NONE/DAILY" AND TRANSACTION "INTERNAL"
        if (strtoupper($incoming_good->order_mode) === 'NONE' && strtoupper($incoming_good->transaction) !== 'INTERNAL')
        {
            ## REQUEST ORDER GENERATE ONLY FOR HAS NOT YET
            if (!$incoming_good->request_order) {
                $this->storeRequestOrder($incoming_good);
            }
        }

        $incoming_good->setCommentLog("Incoming validation [$incoming_good->fullnumber] has been $incoming_good->status [#$incoming_validation->id].");

        $this->DATABASE::commit();
        return response()->json($incoming_good);
    }

    public function destroyPartialValidation($incomingGoodId, $id)
    {

        $incoming_good = IncomingGood::findOrFail($incomingGoodId);
        $incoming_validation = IncomingValidation::findOrFail($id);

        $this->DATABASE::beginTransaction();

        foreach ($incoming_validation->incoming_validation_items as $detail)
        {
            $incoming_good_item = $detail->incoming_good_item;

            ## Remove detail validation
            $detail->delete();

            ## Reclculate unit valid of detail incoming good
            $incoming_good_item->valid = $incoming_good_item->incoming_validation_items->sum('quantity');
            $incoming_good_item->save();
        }

        $incoming_validation->delete();

        ## RECALCULATE INCOMING STOCK
        foreach ($incoming_good->incoming_good_items as $detail)
        {
            $detail->item->distransfer($detail);
            if ($incoming_good->transaction != 'SAMPLE') {
                $to = $incoming_good->transaction == 'RETURN' ? 'NCR' : 'FM';
                $detail->item->transfer($detail, $detail->unit_valid, $to);

                ## CHECK TOTAL STOCK LESS
                if (round($detail->item->getTotalStockist($to)) < 0) {

                    $partName = $detail->item->part_name;
                    $partName .= $detail->item->part_subname ? "(". $detail->item->part_subname .")" : "";
                    $this->error("PART $partName [$to] STOCKLESS");
                }
            }
        }

        ## SET INCOMING STATUS
        $balance = (double) $incoming_good->incoming_good_items->reduce(function ($total, $item) {
            return $total + (round($item->quantity) -  round($item->valid));
        });

        $incoming_good->status = $incoming_good->incoming_validations->count()
            ? ($balance > 0 ? 'PARTIAL-VALIDATED' : 'VALIDATED')
            : 'OPEN';
        $incoming_good->save();

        $incoming_good->setCommentLog("Incoming validation [$incoming_good->fullnumber] has been DELETED [#$incoming_validation->id]!");

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }

    protected function reviseRequestOrder($revise, $incoming_good) {

        $exclude_details = collect(request('incoming_good_items'))->map(function ($item) { return $item['id']; });

        $request_order = $revise->request_order;

        if ($request_order) {
            $request_order->update([
                'date'          => $incoming_good->reference_date,
                'reference_number' => $incoming_good->reference_number,
                'transaction'    => $incoming_good->transaction,
                'order_mode'    => $incoming_good->order_mode,
                'description'   => $request_order->description
                                  ."\nNONE P/O. AUTO REVISION PO BASED ON INCOMING "
                                  ."\nNO: $incoming_good->fullnumber "
                                  ."\nREF: $incoming_good->reference_number ",
            ]);
        }

        $details = $revise->incoming_good_items ?? [];
        foreach ($details as $detail) {
            $request_order_item = $detail->request_order_item;
            $delivery_order_items = (bool) $request_order_item
                ? $detail->request_order_item->delivery_order_items
                : [];

            // Rollback All stock!
            $detail->request_order_item->item->distransfer($detail->request_order_item);
            // Delete item has Removed!
            if (!in_array($detail->id, $exclude_details->toArray())) {
                $delivery_order_items->map(function ($detail) {
                    if (strtoupper($detail->delivery_order->status) == 'CLOSED') {
                        $this->error("DATA has Relation SJDO#". $detail->delivery_order->number ."[CLOSED]. REVISION has not allowed!");
                    }
                    // Unset Relation detail for detail item removed
                    $detail->request_order_item()->associate(null);
                    $detail->save();
                });

                $detail->request_order_item()->forceDelete();
            }

            // Unset Relation detail for revision
            $detail->request_order_item()->associate(null);
            $detail->save();
        }

        $details = $incoming_good->incoming_good_items;
        foreach ($details as $detail) {

            ## Setup unit price
            $price = ($detail->item && $request_order_item->item->price)
                ? $request_order_item->unit_rate * $request_order_item->item->price : 0;

            $fields = collect($detail)->only(['item_id', 'unit_id', 'unit_rate'])->merge(['quantity'=> $detail->valid, 'price' => $price]);

            $request_order_item = ($detail->request_order_item_id)
                ? RequestOrderItem::updateOrCreate(['id' => $detail->request_order_item_id], $fields->toArray())
                : $request_order->request_order_items()->create($fields->toArray());

            $detail->request_order_item()->associate($request_order_item);
            $detail->save();
        }

        $revise->request_order()->associate(null);
        $revise->save();
    }

    private function storeRequestOrder($incoming_good) {
        $incoming_good = $incoming_good->fresh();

        $mode = $incoming_good->order_mode;

        if (strtoupper($mode) === 'NONE') {
            $number = $this->getNextRequestOrderNumber($incoming_good->reference_date);

            $actived = $incoming_good->customer->order_monthly_actived ? date("Y-m-t", strtotime($incoming_good->date)) : null;

            $request_order = RequestOrder::create([
                'number'        => $number,
                'date'          => $incoming_good->reference_date,
                'actived_date' => $actived,
                'customer_id'   => $incoming_good->customer_id,
                'reference_number' => $incoming_good->reference_number,
                'transaction'    => $incoming_good->transaction,
                'order_mode'    => $incoming_good->order_mode,
                'description'   => "NONE P/O. AUTO CREATE PO BASED ON INCOMING "
                                  ."\nNO: $incoming_good->fullnumber "
                                  ."\nREF: $incoming_good->reference_number ",
            ]);
            $incoming_good->request_order()->associate($request_order);
            $incoming_good->save();

            foreach ($incoming_good->incoming_good_items as $row)
            {
                $fields = collect($row)->only(['item_id', 'unit_id', 'unit_rate', 'quantity'])->merge(['price'=>0])->toArray();
                $detail = $request_order->request_order_items()->create($fields);
                ## Setup unit price
                $detail->price = ($detail->item && $detail->item->price)
                    ? $detail->unit_rate * $detail->item->price : 0;

                $detail->save();

                $row->request_order_item()->associate($detail);
                $row->save();
            }

            $request_order->setCommentLog("Sales order [$request_order->fullnumber] has been created!\nOn Validate [$incoming_good->fullnumber]");

        }
    }
}
