<?php
namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryVerifyItem extends Model
{
    use Filterable, SoftDeletes, WithUserBy, HasCommentable;

    protected $fillable = [
        'customer_id', 'date', 'item_id', 'unit_id', 'unit_rate', 'quantity', 'encasement'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double',
    ];

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
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

    public function maxNewVerifyAmount()
    {
        $task = app("App\Models\Income\DeliveryTaskItem")
            ->where('item_id', $this->item_id)
            ->whereHas('delivery_task', function($q) {
                $q->where('date', $this->date);
            })
            ->get()->sum('unit_amount');

        $verifi = app("App\Models\Income\DeliveryVerifyItem")
            ->where('item_id', $this->item_id)
            ->where('date', $this->date)
            ->whereNotIn('id', [$this->id])
            ->get()->sum('unit_amount');

        return round(($task - $verifi) / $this->unit_rate, 2);
    }

    public function validateDestroyVerified()
    {
        $loaded = app("App\Models\Income\DeliveryLoadItem")
            ->where('item_id', $this->item_id)
            ->whereHas('delivery_load', function($q) {
                $q->where('date', $this->date);
            })
            ->get()->sum('unit_amount');

        $verified = app("App\Models\Income\DeliveryVerifyItem")
            ->where('item_id', $this->item_id)
            ->where('date', $this->date)
            ->whereNotIn('id', [$this->id])
            ->get()->sum('unit_amount');

        // abort(502, "$verified >= ". floor($loaded));
        return (boolean) ($verified >= floor($loaded));
    }
}
