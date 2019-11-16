<?php

namespace App\Http\Requests\Factory;

use App\Http\Requests\Request;

class Packing extends Request
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
            $id = $this->packing;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:packings,NULL,' . $id,
            'customer_id' => 'required',
            'date' => 'required',
            'shift_id' => 'required',
            'packing_items.item_id' => 'required',
            'packing_items.quantity' => 'required|numeric|min:0',
            'packing_items.unit_id' => 'required',
            'packing_items.packing_item_faults.*.quantity' => 'required_if:packing_items.packing_item_faults.*.fault_id,!=,null',
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'packing_items.item_id' => $msg,
            'customer_id.required'  => $msg,
        ];
    }
}
