<?php
namespace App\Models\Common;

use App\Models\Model;

class RuteCustomer extends Model
{
    public $timestamps = false;

    protected $fillable = ['customer_id', 'code'];

    public function rute() {
        return $this->belongsTo('App\Models\Common\Rute');
    }

    public function customer() {
        return $this->belongsTo('App\Models\Income\Customer');
    }
}
