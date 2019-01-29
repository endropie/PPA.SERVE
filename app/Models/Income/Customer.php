<?php

namespace App\Models\Income;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    
    protected $fillable = [
        'code', 'name', 'address', 'subdistrict', 'district', 'province_id', 'email', 'phone', 'fax', 'bank_account', 'npwp', 'pkp', 
        'cso_name', 'cso_phone', 'ppic_name', 'ppic_phone', 'qc_name', 'qc_phone', 
        'with_tax', 'with_pph', 'tax', 'pph_material', 'pph_service', 'bill_mode', 'delivery_mode', 'order_mode', 
        'description'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $model_relations = ['items'];

    public function items()
    {
        return $this->hasMany('App\Models\Common\Item');
    }  
}
