<?php
namespace App\Api\Controllers\Warehouses;

use App\Api\Controllers\ApiController;
use App\Api\Transformers\Warehouses\OpnameVouchers as Transformer;
use App\Filters\Warehouse\OpnameVoucher as Filter;
use App\Models\Warehouse\OpnameVoucher;
use App\Traits\GenerateNumber;
use Illuminate\Http\Request;

class OpnameVouchers extends ApiController
{
    use GenerateNumber;

    public function index (Filter $filter) {

        switch (request('mode')) {
            case 'limitation':
                $opname_vouchers = OpnameVoucher::filter($filter)->get();
                break;

            default:
                $opname_vouchers = OpnameVoucher::filter($filter)->latest()->pagination();
                break;
        }

        return method_exists($opname_vouchers, 'hasPages')
        ? $this->response->paginator($opname_vouchers, new Transformer(), ['key' => 'data'])
        : $this->response->collection($opname_vouchers, new Transformer());
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if (!$request->number) $request->merge(['number' => $this->getNextOpnameVoucherNumber()]);

        $request->validate([
            'number' => 'required',
            'item_id' => 'required',
            'unit_id' => 'required',
            'unit_rate' => 'required',
            'quantity' => 'required',
            'stockist' => 'required',
        ]);

        $opname_voucher = OpnameVoucher::create($request->all());

        $label = $opname_voucher->item->part_name ?? $opname_voucher->item->part_number ?? $opname_voucher->item->id;
        if (!$opname_voucher->item->enable) $this->error("PART [". $label . "] DISABLED");

        if ($request->get('status') == 'VALIDATED') {
            $opname_voucher->status = 'VALIDATED';
            $opname_voucher->save();
        }

        $this->DATABASE::commit();

        return $this->response->item($opname_voucher, new Transformer());
    }

    public function show(Request $request, $id)
    {
        $opname_voucher = OpnameVoucher::findOrFail($id);

        $transformer = new Transformer();
        return $this->response->item($opname_voucher, $transformer);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') === 'validation') return $this->validation($request, $id);

        $request->validate([
            'number' => 'required',
            'item_id' => 'required',
            'unit_id' => 'required',
            'unit_rate' => 'required',
            'quantity' => 'required',
            'stockist' => 'required',
        ]);

        $this->DATABASE::beginTransaction();

        $opname_voucher = OpnameVoucher::findOrFail($id);

        if ($opname_voucher->status != "OPEN") $this->error("$opname_voucher->number is not OPEN state, is not allowed to be changed");
        if ($opname_voucher->is_relationship) $this->error("$opname_voucher->number has relationships, is not allowed to be changed");

        $opname_voucher->update($request->input());

        $label = $opname_voucher->item->part_name ?? $opname_voucher->item->part_number ?? $opname_voucher->item->id;
        if (!$opname_voucher->item->enable) $this->error("PART [". $label . "] DISABLED");

        $this->DATABASE::commit();

        return $this->response->item($opname_voucher, new Transformer());
    }

    public function destroy($id)
    {
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

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }

    public function validation($request, $id)
    {
        $this->DATABASE::beginTransaction();
        $opname_voucher = OpnameVoucher::findOrFail($id);

        if ($opname_voucher->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be changed');

        $opname_voucher->status = 'VALIDATED';
        $opname_voucher->save();

        $this->DATABASE::commit();

        return $this->response->item($opname_voucher, new Transformer());
    }
}
