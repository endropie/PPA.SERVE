<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\OpnameVoucher as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Warehouse\OpnameVoucher as Filters;
use App\Models\Warehouse\OpnameVoucher;
use App\Traits\GenerateNumber;

class OpnameVouchers extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $opname_vouchers = OpnameVoucher::filter($filters)->get();
                break;

            case 'datagrid':
                $opname_vouchers = OpnameVoucher::filter($filters)->latest()->get();
                $opname_vouchers->each->append(['is_relationship']);
                break;

            case 'counter':
                $opname_vouchers = OpnameVoucher::filter($filters)->count();
                break;

            default:
                $opname_vouchers = OpnameVoucher::with(['user_by','item','unit'])->filter($filters)->latest()->collect();
                $opname_vouchers->getCollection()->transform(function($item) {
                    $item->append(['is_relationship', 'opname_number']);
                    return $item;
                });
                break;
        }

        return response()->json($opname_vouchers);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname_voucher = OpnameVoucher::create($request->all());

        $label = $opname_voucher->item->part_name ?? $opname_voucher->item->part_number ?? $opname_voucher->item->id;
        if (!$opname_voucher->item->enable) $this->error("PART [". $label . "] DISABLED");

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($opname_voucher);
    }

    public function show($id)
    {
        $opname_voucher = OpnameVoucher::withTrashed()->with([
            'unit',
            'item.item_units',
        ])->findOrFail($id);

        $opname_voucher->append(['is_relationship','has_relationship']);

        return response()->json($opname_voucher);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') === 'validation') return $this->validation($request, $id);

        $this->DATABASE::beginTransaction();

        $opname_voucher = OpnameVoucher::findOrFail($id);

        if ($opname_voucher->status != "OPEN") $this->error("$opname_voucher->reference is not OPEN state, is not allowed to be changed");
        if ($opname_voucher->is_relationship) $this->error("$opname_voucher->reference has relationships, is not allowed to be changed");

        $opname_voucher->update($request->input());

        $label = $opname_voucher->item->part_name ?? $opname_voucher->item->part_number ?? $opname_voucher->item->id;
        if (!$opname_voucher->item->enable) $this->error("PART [". $label . "] DISABLED");

        // Before Update Force delete opname stocks items
        $this->DATABASE::commit();
        return response()->json($opname_voucher);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname_voucher = OpnameVoucher::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($opname_voucher->is_relationship) $this->error("$opname_voucher->reference has relationship, is not allowed to be $mode");
        if($mode == "DELETED" && $opname_voucher->status != 'OPEN') $this->error("The data $opname_voucher->status state, is not allowed to be $mode");

        if($mode == 'VOID') {
            $opname_voucher->status = "VOID";
            $opname_voucher->save();
        }

        $opname_voucher->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function validation($request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();
        $opname_voucher = OpnameVoucher::findOrFail($id);

        if ($opname_voucher->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        $opname_voucher->status = 'VALIDATED';
        $opname_voucher->save();

        $this->DATABASE::commit();
        return response()->json($opname_voucher);
    }
}
