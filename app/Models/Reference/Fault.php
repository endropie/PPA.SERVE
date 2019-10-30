<?php
namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Fault extends Model
{
    use Filterable;

    protected $fillable = ['type_fault_id', 'name', 'description'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = ['packing_item_faults'];

    public function type_fault()
    {
        return $this->belongsTo('App\Models\Reference\TypeFault');
    }

    public function packing_item_faults()
    {
        return $this->hasMany('App\Models\Factory\PackingItemFault', 'fault_id');
    }
}
