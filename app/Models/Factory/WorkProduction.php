<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;

class WorkProduction extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = ['number', 'line_id', 'date', 'shift_id', 'worktime', 'oprator_id', 'description'];

    protected $appends = ['fullnumber'];

    protected $hidden = ['updated_at'];

    protected $relationships = [
        'work_production_items.work_order_item.work_order_closed',
        'work_production_items.work_order_item.work_order_producted',
    ];

    public function work_production_items()
    {
        return $this->hasMany('App\Models\Factory\WorkProductionItem')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Reference\Line');
    }

    public function shift()
    {
        return $this->belongsTo('App\Models\Reference\Shift');
    }

    public function isExistFullnumber()
    {
        return (boolean) self::where('number', $this->number)
            ->where('id', "<>", $this->id)
            ->count();
    }

    public function getFullnumberAttribute()
    {
        return $this->number;
    }
}
