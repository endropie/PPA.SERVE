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
        else $id = null;

        return [
            'number' => ($id ? 'required|' : '') .'unique:opname_stocks,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'date' => 'required',

            'opname_stock_items.*.item_id' => 'required',
            'opname_stock_items.*.unit_id' => 'required',
            'opname_stock_items.*.unit_rate' => 'required',
            'opname_stock_items.*.stockist' => 'required',
            'opname_stock_items.*.init_amount' => 'required',
            'opname_stock_items.*.final_amount' => 'required',
            'opname_stock_items' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0)  $fail('List Part must be select min. 1 Part item.');
            },
        ];
    }
}
