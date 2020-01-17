<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\Opname as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter as Filters;
use App\Models\Common\Item;
use App\Models\Warehouse\Opname;
use App\Models\Warehouse\OpnameVoucher;
use App\Traits\GenerateNumber;

class Opnames extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $opnames = Opname::filter($filters)->get();
                break;

            case 'datagrid':
                $opnames = Opname::filter($filters)->latest()->get();
                $opnames->each->setAppends(['is_relationship']);
                break;

            default:
                $opnames = Opname::filter($filters)->latest()->collect();
                $opnames->getCollection()->transform(function($item) {
                    // $item->setAppends();
                    return $item;
                });
                break;
        }

        return response()->json($opnames);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        $opname = Opname::create(['number' => $this->getNextOpnameNumber()]);

        $this->generate($opname);

        $this->DATABASE::commit();
        return response()->json($opname);
    }

    public function show($id)
    {
        $opname = Opname::withTrashed()->with([
            // 'opname_stocks'
        ])->findOrFail($id);

        $opname->append(['is_relationship','has_relationship']);

        return response()->json($opname);
    }

    public function update(Request $request, $id)
    {
        if(request('mode') === 'validation') return $this->validation($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname = Opname::findOrFail($id);

        if ($opname->status != "OPEN") $this->error("$opname->number is not OPEN state, is not allowed to be changed");
        if ($opname->is_relationship) $this->error("$opname->number has relationships, is not allowed to be changed");

        $this->generate($opname);

        $this->DATABASE::commit();
        return response()->json($opname);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname = Opname::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($opname->is_relationship) $this->error("$opname->number has relationship, is not allowed to be $mode");
        if($mode == "DELETED" && $opname->status != 'OPEN') $this->error("The data $opname->status state, is not allowed to be $mode");

        if($mode == 'VOID') {
            $opname->status = "VOID";
            $opname->save();
        }

        foreach ($opname->opname_stocks as $detail) {
            $detail->item->distransfer($detail);
            $detail->opname_vouchers->each(function ($item) {
                $item->opname_stock()->dissociate();
                $item->save();
            });
            $detail->delete();
        }

        $opname->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }

    public function validation($request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname = Opname::findOrFail($id);

        if ($opname->status != "OPEN") $this->error('The data not "OPEN" state, is not allowed to be validation');

        foreach ($opname->opname_stocks as $detail) {
            $stockist = $detail->stockist;
            $detail->item->distransfer($detail);
            $detail->item->transfer($detail, $detail->move_amount, $stockist);
        }

        $opname->status = 'VALIDATED';
        $opname->save();

        // $this->error('LOLOS');

        $this->DATABASE::commit();
        return response()->json($opname);
    }

    protected function generate($opname) {

        $all = OpnameVoucher::where('status', 'VALIDATED')
            ->whereNull('opname_stock_id')
            ->get();

        if ($all->count() <= 0) $this->error('The VALIDATED voucher not found!');

        $group = $all->groupBy(function($item, $key) {
            return $item['item_id']."--".$item['stockist'];
        });

        foreach ($group as $key => $vouchers) {

            $code = explode('--', $key);
            $item = Item::find($code[0]);
            $stockist = $code[1];
            $opname_stock = $opname->opname_stocks()->firstOrCreate([
                'opname_id' => $opname->id,
                'item_id' => $item->id,
                'stockist' => $stockist,
            ], [
                'item_id' => $item->id,
                'stockist' => $stockist,
                'init_amount' => $item->totals[$stockist],
            ]);

            foreach ($vouchers as $voucher) {
                $voucher->opname_stock()->associate($opname_stock);
                $voucher->save();
            }

            $init_amount = $opname_stock->init_amount;
            $final_amount = $opname_stock->opname_vouchers->sum('unit_amount');

            $opname_stock->opname()->associate($opname);
            $opname_stock->move_amount = (double) ($final_amount - $init_amount);
            $opname_stock->save();
        }

    }
}
