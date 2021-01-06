<?php
namespace App\Models\Income;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryLoadItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'unit_id', 'unit_rate', 'quantity', 'encasement'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double',
    ];

    public function delivery_load()
    {
        return $this->belongsTo('App\Models\Income\DeliveryLoad');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getUnitAmountAttribute() {
        // return false when rate is not valid
        $unit_rate = (double) ($this->unit_rate ?? 1);

        return (double) ($this->quantity * $unit_rate);
    }

    public function maxAmountDetail ()
    {
        // $FG = (double) $this->item->totals['FG'];

        $verified = (double) app("App\Models\Income\DeliveryVerifyItem")
            ->where('item_id', $this->item_id)
            ->where('date', $this->delivery_load->date)
            ->get()->sum('unit_amount');

        $loaded = (double) app("App\Models\Income\DeliveryLoadItem")
            ->where('item_id', $this->item_id)
            ->whereHas('delivery_load', function($q) {
                $q->where('date', $this->delivery_load->date);
            })
            ->whereNotIn('id', [$this->id])
            ->get()->sum('unit_amount');

        $transLoaded = (double) app("App\Models\Income\DeliveryLoadItem")
            ->where('item_id', $this->item_id)
            ->whereHas('delivery_load', function($q) {
                $q->where('date', $this->delivery_load->date);
                $q->where('transaction', $this->delivery_load->transaction);
            })
            ->whereNotIn('id', [$this->id])
            ->get()->sum('unit_amount');

        $transTask = (double) app("App\Models\Income\DeliveryTaskItem")
            ->where('item_id', $this->item_id)
            ->whereHas('delivery_task', function($q) {
                $q->where('date', $this->delivery_load->date);
                $q->where('transaction', $this->delivery_load->transaction);
            })
            ->get()->sum('unit_amount');

        $ownVerified = ($verified - $loaded) > 0 ? ($verified - $loaded) : 0;
        $ownTask = ($transTask - $transLoaded) > 0 ? ($transTask - $transLoaded) : 0;

        return round(min($ownVerified, $ownTask) / $this->unit_rate, 2);
    }

    public function maxFGDetail ()
    {
        $FG = (double) $this->item->totals['FG'];

        $loaded = (double) app("App\Models\Income\DeliveryLoadItem")
            ->where('item_id', $this->item_id)
            ->where('delivery_load_id', $this->delivery_load_id)
            ->whereNotIn('id', [$this->id])
            ->get()->sum('unit_amount');

        return round(($FG - $loaded) / $this->unit_rate, 2);
    }

    public function setLoadVerified ()
    {
        app("App\Models\Income\DeliveryVerifyItem")
            ->where('item_id', $this->item_id)
            ->where('date', $this->delivery_load->date)
            ->update(['loaded_at' => $this->delivery_load->created_at]);

    }
}
