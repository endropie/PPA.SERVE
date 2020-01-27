<?php

namespace App\Http\Requests\Income;

use App\Http\Requests\Request;

class RequestOrder extends Request
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
            $id = $this->request_order;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:request_orders,NULL,' . $id,
            'customer_id' => 'required',

            'request_order_items.*.item_id' => 'required',
            'request_order_items.*.unit_id' => 'required',
            'request_order_items.*.unit_rate' => 'required',
            'request_order_items.*.quantity' =>
                function ($attribute, $value, $fail) {
                    if (floatval($value) <= 0) {
                        $fail('Quantity must be more than 0 unit.');
                    }
                },
            // 'request_order_items.*.price' =>
            //     function ($attribute, $value, $fail) {
            //         if (floatval($value) <= 0) {
            //             $fail('Price must be more than 0 unit.');
            //         }
            //     },
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'request_order_items.*.item_id' => $msg,
        ];
    }
}
