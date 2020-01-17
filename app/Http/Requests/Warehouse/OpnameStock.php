<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;

class OpnameStock extends Request
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
            $id = $this->opname_stock;

            if($this->exists('nodata')) return [];
        }
        else {
            $id = null;
            return [];
        }

        return [
            'number' => ($id ? 'required|' : '') .'unique:opname_stocks,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'item_id' => 'required',
            'stockist' => 'required',
            'init_amount' => 'required',
            'opname_vouchers.*.item_id' => 'required',
            'opname_vouchers.*.stockist' => 'required',
            'opname_vouchers' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0)  $fail('List Detail must be min. 1 part item.');
            },
        ];
    }
}
