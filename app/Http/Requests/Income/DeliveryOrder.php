<?php

namespace App\Http\Requests\Income;

use App\Http\Requests\Request;

class DeliveryOrder extends Request
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
            $id = $this->delivery_order;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:delivery_orders,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'date' => 'required',
            'transaction' => 'required',
            'customer_id' => 'required',
            'request_order_id' => ($this->get('is_internal') || $this->get('transaction') == 'SAMPLE' ? '' : 'required'),
            'delivery_order_items.*.item_id' => 'required',
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'delivery_order_items.*.item_id' => $msg,
            'quantity.required'         => $msg,
        ];
    }
}
