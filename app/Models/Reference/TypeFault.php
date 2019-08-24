<?php
namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class TypeFault extends Model
{
    use Filterable;

    protected $fillable = ['name', 'description'];

    protected $hidden = ['created_at', 'updated_at'];

    public function faults()
    {
        return $this->hasMany('App\Models\Reference\Fault');
    }

}
