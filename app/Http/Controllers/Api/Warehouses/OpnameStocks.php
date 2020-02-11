<?php

namespace App\Http\Controllers\Api\Warehouses;

use App\Http\Requests\Warehouse\OpnameStock as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Warehouse\OpnameStock as Filters;
use App\Models\Common\Item;
use App\Models\Warehouse\Opname;
use App\Models\Warehouse\OpnameStock;
use App\Models\Warehouse\OpnameVoucher;
use App\Traits\GenerateNumber;

class OpnameStocks extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $opname_stocks = OpnameStock::filter($filters)->get();
                break;

            case 'datagrid':
                $opname_stocks = OpnameStock::filter($filters)->latest()->get();
                $opname_stocks->each->append(['is_relationship']);
                break;

            default:
                $opname_stocks = OpnameStock::with('user_by','opname','item.unit')->filter($filters)->latest()->collect();
                $opname_stocks->getCollection()->transform(function($item) {
                    $item->append(['opname_number','is_relationship']);
                    return $item;
                });
                break;
        }

        return response()->json($opname_stocks);
    }

    public function store(Request $request)
    {
        $this->error('NOT PROCESED');
        $this->DATABASE::beginTransaction();

        if (!$opname = Opname::find($request->opname_id)) {
            $opname = Opname::create(['number' => $this->getNextOpnameNumber()]);
        }


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

        $this->DATABASE::commit();

        return response(['message' => 'OK', 'voucher_count' => $all->count()]);
    }

    public function show($id)
    {
        $opname_stock = OpnameStock::withTrashed()->with([
            'item.unit',
            'opname_vouchers'
        ])->findOrFail($id);

        $opname_stock->append(['opname_number','is_relationship','has_relationship']);

        return response()->json($opname_stock);
    }

    public function update(Request $request, $id)
    {
        $this->error('NOT PROCESSED!');
        if(request('mode') === 'validation') return $this->validation($request, $id);
        if(request('mode') === 'revision') return $this->revision($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname_stock = OpnameStock::findOrFail($id);

        if ($opname_stock->status != "OPEN") $this->error("$opname_stock->number is not OPEN state, is not allowed to be changed");
        if ($opname_stock->is_relationship) $this->error("$opname_stock->number has relationships, is not allowed to be changed");

        $this->DATABASE::commit();
        return response()->json($opname_stock);
    }

    public function destroy($id)
    {
        $this->error('NOT PROCESSED!');
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $opname_stock = OpnameStock::findOrFail($id);

        $mode = strtoupper(request('mode') ?? 'DELETED');
        if($opname_stock->is_relationship) $this->error("$opname_stock->number has relationship, is not allowed to be $mode");


        $opname_stock->opname_vouchers->each(function($v) {
            $v->opname_stock()->dissociate();
            $v->save();
        });

        $opname_stock->item->distransfer($opname_stock);
        $opname_stock->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
