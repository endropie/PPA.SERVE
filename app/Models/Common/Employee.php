<?php
namespace App\Models\Common;

use App\Models\Model;
use App\Filters\Filterable;

class Employee extends Model
{
    use Filterable;

    protected $fillable = [
        'code', 'name', 'phone', 'email',
        'department_id', 'position_id', 'line_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [
        'work_productions',
        'packings',
        'outgoing_goods'
    ];

    public function user () {
        return $this->belongsTo('App\Models\Auth\User');
    }

    public function department () {
        return $this->belongsTo('App\Models\Reference\Department');
    }

    public function position () {
        return $this->belongsTo('App\Models\Reference\Position');
    }

    public function line () {
        return $this->belongsTo('App\Models\Reference\line');
    }

    public function work_productions() {
        return $this->hasMany('App\Models\Factory\WorkProduction', 'created_at');
    }

    public function packings() {
        return $this->hasMany('App\Models\Factory\Packing', 'operator_id');
    }

    public function outgoing_goods() {
        return $this->hasMany('App\Models\Warehouse\OutgoingGood', 'created_at');
    }

}
