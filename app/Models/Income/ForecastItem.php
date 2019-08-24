<?php
namespace App\Models\Income;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForecastItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'unit_id', 'unit_rate', 'quantity', 'price', 'note'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function forecast()
    {
        return $this->belongsTo('App\Models\Income\Forecast');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item')->with('unit');
    }

    public function stockable()
    {
        return $this->morphMany('App\Models\Common\ItemStockable', 'base');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getUnitAmountAttribute() {

        // return false when rate is not valid
        if($this->unit_rate <= 0) return false;

        return (double) $this->quantity / $this->unit_rate;
    }
}
