<?php
namespace App\Models\Income;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryTaskItem extends Model
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
        'verified_amount' => 'double',
        'loaded_amount' => 'double'
    ];

    public function delivery_task()
    {
        return $this->belongsTo('App\Models\Income\DeliveryTask');
    }

    public function delivery_verifies()
    {
        return $this->hasMany('App\Models\Warehouse\DeliveryVerifies');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getUnitAmountAttribute()
    {
        // return false when rate is not valid
        $unit_rate = (double) ($this->unit_rate ?? 1);

        return (double) ($this->quantity * $unit_rate);
    }

    public function validateDetailUpdate ()
    {
        $task = app("App\Models\Income\DeliveryTaskItem")
            ->where('item_id', $this->item_id)
            ->whereHas('delivery_task', function($q) {
                $q->where('date', $this->delivery_task->date);
            })
            ->get()->sum('unit_amount');

        $verifi = app("App\Models\Income\DeliveryVerifyItem")
            ->where('item_id', $this->item_id)
            ->where('date', $this->delivery_task->date)
            ->get()->sum('unit_amount');


        ## Retunrn is boolean. if "false" is over verified
        return (boolean) (round($task - $verifi, 0, PHP_ROUND_HALF_UP) >= 0);
    }

    public function validateDetailDestroy()
    {
        $task = app("App\Models\Income\DeliveryTaskItem")
            ->where('item_id', $this->item_id)
            ->whereHas('delivery_task', function($q) {
                $q->where('date', $this->delivery_task->date);
            })
            ->whereNotIn('id', [$this->id])
            ->get()->sum('unit_amount');

        $verifi = app("App\Models\Income\DeliveryVerifyItem")
            ->where('item_id', $this->item_id)
            ->where('date', $this->delivery_task->date)
            ->get()->sum('unit_amount');


        ## Retunrn is boolean. if "false" is over verified
        return (boolean) (round($task - $verifi, 0, PHP_ROUND_HALF_UP) >= 0);
    }
}
