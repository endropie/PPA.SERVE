<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;

class OpnameVoucher extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Check if store or update
        $method = $this->getMethod();

        if ($method == 'PATCH' || $method == 'PUT') {
            $id = $this->opname_voucher;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => 'required',
            'item_id' => 'required',
            'stockist' => 'required',

            'quantity' => 'required',
            'unit_id' => 'required',
            'unit_rate' => 'required',
        ];
    }
}
