<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;

class ForecastLoadItem extends Model
{
    use Filterable;

    protected $fillable = [
        'line_id', 'item_id', 'amount', 'amount_load', 'amount_packtime', 'capacity', 'ismain'
    ];

    protected $hidden = [];

    protected $casts = [
        'amount' => 'double',
        'amount_load' => 'double',
        'amount_packtime' => 'double',
        'capacity' => 'double',
    ];

    protected $relationships = [];

    public function forecast_load()
    {
        return $this->belongsTo('App\Models\Income\ForecastLoad');
    }

}
