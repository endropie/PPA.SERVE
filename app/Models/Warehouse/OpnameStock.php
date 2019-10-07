<?php
namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Filters\Filterable;

class OpnameStock extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'date', 'reference', 'description'
    ];

    protected $relationships = [];

    protected $hidden = [];

    public function opname_stock_items()
    {
        return $this->hasMany('App\Models\Warehouse\OpnameStockItem')->withTrashed();
    }
}
