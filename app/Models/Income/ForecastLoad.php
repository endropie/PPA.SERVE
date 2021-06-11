<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;

class ForecastLoad extends Model
{
    use Filterable, WithUserBy;

    protected $fillable = [
        'period_id', 'number'
    ];

    protected $hidden = [];

    protected $relationships = [];

    public function forecast_load_items()
    {
        return $this->hasMany('App\Models\Income\ForecastLoadItem');
    }

    public function period()
    {
        return $this->belongsTo('App\Models\Income\ForecastPeriod');
    }

    public function saveDetail()
    {
        $this->forecast_load_items()->delete();

        foreach ($this->period->forecast_items as $detail) {
            foreach ($detail->item->item_prelines as $preline) {

                $capacity = ($preline->line->load_capacity ?: 0) * ($this->period->days ?: 0);
                $amount_load = $preline->load_amount ? ($detail->quantity * $detail->unit_rate) / $preline->load_amount : 0;
                $amount_packtime = $detail->quantity * $detail->unit_rate * ($detail->item->packing_duration ?? 0);

                $this->forecast_load_items()->create([
                    'line_id' => $preline->line_id,
                    'item_id' => $detail->item->id,
                    'amount' => $detail->quantity * $detail->unit_rate,
                    'amount_load' => $amount_load,
                    'amount_packtime' => $amount_packtime,
                    'capacity' => $capacity,
                    'ismain' => $preline->ismain,
                ]);
            }
        }

        return $this;
    }

}
