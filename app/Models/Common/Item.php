<?php

namespace App\Models\Common;

use App\Models\Model;
use App\Filters\Filterable;
use App\Models\DataSamples;
use App\Models\WithUserBy;
use Endropie\AccurateClient\Traits\AccurateTrait;

class Item extends Model
{
    use Filterable, WithUserBy, DataSamples, AccurateTrait;

    protected $allowTransferDisabled;

    protected $allowTransferStockLess;

    protected $accurate_model = "item";

    protected $accurate_push_attributes = [
        'name' => 'part_name',
        'no' => 'code',
        'unitPrice' => 'price'
    ];

    static function boot()
    {
        parent::boot();
        static::registerModelEvent('accurate.pushing', function ($model, $record) {
            return array_merge($record, [
                'tax1Name' => 'Pajak Pertambahan Nilai',
                'tax3Name' => 'Jasa Teknik',
                'itemType' => 'SERVICE'
            ]);
        });
    }

    protected $fillable = [
        'code', 'customer_id', 'brand_id', 'specification_id', 'part_name', 'part_alias',  'part_number',
        'load_type', 'load_capacity', 'packing_duration', 'sa_dm', 'weight', 'price',
        'category_item_id', 'type_item_id', 'size_id', 'unit_id', 'category_item_price_id', 'description', 'enable',
        'estimate_monthly_amount',  'estimate_load_capacity', 'estimate_sadm', 'estimate_price', 'estimate_begin_date',
        'project', 'project_number', 'sample'
    ];

    protected $appends = ['part_subname', 'customer_code', 'totals'];

    protected $hidden = ['updated_at'];

    protected $casts = [
        'sa_dm' => 'double',
        'weight' => 'double',
        'price' => 'double',
        'depicts' => 'array'
    ];

    protected $relationships = [
        'item_stockables', 'item_stocks',
        'incoming_good_items', 'outgoing_good_items',
        'work_order_items', 'work_production_items', 'packing_items',
        'forecast_items', 'request_order_items', 'delivery_order_items',
    ];

    public function incoming_good_items()
    {
        return $this->hasMany('App\Models\Warehouse\IncomingGoodItem');
    }

    public function outgoing_good_items()
    {
        return $this->hasMany('App\Models\Warehouse\OutgoingGoodItem');
    }

    public function work_order_items()
    {
        return $this->hasMany('App\Models\Factory\WorkOrderItem');
    }

    public function work_production_items()
    {
        return $this->hasMany('App\Models\Factory\WorkProductionItem');
    }

    public function packing_items()
    {
        return $this->hasMany('App\Models\Factory\PackingItem');
    }

    public function forecast_items()
    {
        return $this->hasMany('App\Models\Income\ForecastItem');
    }

    public function request_order_items()
    {
        return $this->hasMany('App\Models\Income\RequestOrderItem');
    }

    public function delivery_order_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrderItem');
    }

    public function delivery_task_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryTaskItem');
    }

    public function delivery_verify_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryVerifyItem');
    }

    public function delivery_load_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryLoadItem');
    }

    public function units()
    {
        return $this->belongsToMany('App\Models\Reference\Unit', 'item_units');
    }

    public function item_prelines()
    {
        return $this->hasMany('App\Models\Common\ItemPreline');
    }

    public function item_units()
    {
        return $this->hasMany('App\Models\Common\ItemUnit');
    }

    public function category_item_price()
    {
        return $this->belongsTo('App\Models\Common\CategoryItemPrice');
    }

    public function item_stocks()
    {
        return $this->hasMany('App\Models\Common\ItemStock');
    }

    public function item_stockables()
    {
        return $this->hasMany('App\Models\Common\ItemStockable');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Reference\Brand');
    }

    public function specification()
    {
        return $this->belongsTo('App\Models\Reference\Specification');
    }

    public function category_item()
    {
        return $this->belongsTo(\App\Models\Reference\CategoryItem::class);
    }

    public function type_item()
    {
        return $this->belongsTo(\App\Models\Reference\TypeItem::class);
    }

    public function size()
    {
        return $this->belongsTo(\App\Models\Reference\Size::class);
    }

    public function getPartSubnameAttribute()
    {
        $mode = setting()->get('item.subname_mode', null);
        if ($mode == 'PART_NUMBER') {
            return $this->part_number;
        }
        if ($mode == 'SPECIFICATION') {
            return $this->specification ? $this->specification->name : null;
        }
        return null;
    }

    public function getUnitConvertionsAttribute()
    {
        $units = collect([]);
        $units->push(array_merge($this->unit->toArray(), ['rate' => 1]));

        foreach ($this->item_units as $item) {
            $units->push(array_merge($item->unit->toArray(), ['rate' => (float) $item->rate]));
        }
        return $units;
    }

    public function getTotalsAttribute()
    {
        $stocks = [];
        $all = 0;
        foreach (ItemStock::getStockists() as $key => $value) {
            $stocks[$key] = (float) $this->hasMany('App\Models\Common\ItemStock')->where('stockist', $key)->sum('total');
            if (array_search($key, ['FM', 'WIP', 'PFG', 'FG', 'NC', 'NCR']) > -1) $all += $stocks[$key];
        }
        return array_merge($stocks, ['*' => $all]);
    }

    public function getTotalWorkOrderAttribute()
    {
        return $this->hasMany('App\Models\Factory\WorkOrderItem')
            ->whereHas('work_order', function ($q) {
                return $q->where('status', 'OPEN')->whereNull('main_id');
            })->get()
            ->groupBy(function ($item) {
                return $item->work_order->stockist_from;
            })
            ->map(function ($items) {
                return $items->sum('unit_amount') - $items->sum('amount_process');
            });
    }

    public function getCustomerCodeAttribute()
    {
        $customer = $this->customer()->first();

        if (!$customer) return null;
        return $customer->code;
    }

    public function amount_delivery_verify($date = null)
    {
        if (!$date) return 0;
        return (float) $this->delivery_verify_items()->where('date', $date)->get()->sum('unit_amount');
    }

    public function amount_delivery_task($date = null, $trans = null)
    {
        if (!$date) return 0;
        return (float) $this->delivery_task_items()->whereHas('delivery_task', function ($q) use ($trans, $date) {
            return $q->where('date', $date)
                ->when($trans !== null, function ($q) use ($trans) {
                    return $q->where('transaction', $trans);
                });
        })->get()->sum('unit_amount');
    }

    public function amount_delivery_load($date = null, $trans = null)
    {
        if (!$date) return 0;
        return (float) $this->delivery_load_items()->whereHas('delivery_load', function ($q) use ($trans, $date) {
            return $q->where('date', $date)
                ->when($trans !== null, function ($q) use ($trans) {
                    return $q->where('transaction', $trans);
                });;
        })->get()->sum('unit_amount');
    }

    public function stock($stockist)
    {
        $stockist = ItemStock::getValidStockist($stockist);

        return $this->item_stocks->where('stockist', $stockist)->first();
    }

    public function getUnitPrice($unit = null)
    {
        $price = (float) $this->price ?? 0;
        if (!$unit) return $price;
        else {
            $id = $unit->id ?? $unit;
            $rate = ($u = $this->item_units()->where('unit_id', $id)->get()->first())
                ? $u->rate : 1;
            return  $price * $rate;
        }
    }

    public function transfer($collect, $number, $stockist = false, $exStockist = false)
    {

        if (!$this->enable && !$this->allowTransferDisabled) abort(501, "PART [$this->code] DISABLED");

        $collect = $collect->fresh();

        if ($exStockist) {
            $exStockist = ItemStock::getValidStockist($exStockist);
            $exStock = $this->item_stocks()->firstOrCreate(['stockist' => $exStockist]);
            $exStock->total = $exStock->total - $number;

            if (!$this->allowTransferStockLess && round($exStock->total) <  0) abort(501, "PART [$this->code] STOCK LESS");

            $exStock->save();

            $this->item_stockables()->create([
                'base_id' => $collect->id,
                'base_type' => get_class($collect),
                'unit_amount' => (-1) * ($number),
                'stockist' => $exStockist,
            ]);

            if ($stockist == false) return $exStock;
        }

        if ($stockist !== false) {

            $stockist = ItemStock::getValidStockist($stockist);
            $stock = $this->item_stocks()->firstOrCreate(['stockist' => $stockist]);
            $stock->total = $stock->total + $number;
            $stock->save();

            $this->item_stockables()->create([
                'base_id' => $collect->id,
                'base_type' => get_class($collect),
                'unit_amount' => ($number),
                'stockist' => $stockist,
            ]);

            return $stock;
        }
    }

    public function distransfer($collect, $delete = true)
    {
        if ($collect->stockable->count() == 0) return;

        // if(!$this->enable && !$this->allowTransferDisabled) abort(501, "PART [$this->code] DISABLED");

        foreach ($collect->stockable as $log) {
            $stock = $this->item_stocks()->firstOrCreate(['stockist' => $log->stockist]);
            $stock->total -= $log->unit_amount;
            $stock->save();

            if ($delete) $log->delete();
        }
    }

    public function allowDisableTransfer()
    {
        $this->allowTransferDisabled = true;
        return $this;
    }

    public function allowStockLessTransfer()
    {
        $this->allowTransferStockLess = true;
        return $this;
    }
}
