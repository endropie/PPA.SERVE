<?php

namespace App\Models\Income;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;

class ForecastPeriod extends Model
{
    use Filterable;

    protected $fillable = [
        'period', 'days'
    ];

    protected $hidden = [];

    protected $relationships = ['forecasts', 'forecast_loads'];

    public function forecasts()
    {
        return $this->hasMany('App\Models\Income\Forecast', 'period_id')->withTrashed();
    }

    public function forecast_items()
    {
        return $this->hasManyThrough('App\Models\Income\ForecastItem', 'App\Models\Income\Forecast', 'period_id');
    }

    public function forecast_loads()
    {
        return $this->hasMany('App\Models\Income\ForecastLoad', 'period_id');
    }

}
