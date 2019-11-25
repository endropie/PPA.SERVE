<?php
namespace App\Http\Requests\Income;

use App\Http\Requests\Request;

class PreDelivery extends Request
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
            $id = $this->pre_delivery;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|' : '') .'unique:pre_deliveries,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'customer_id' => 'required',
            'date' => 'required',
            'delivery_items.*.item_id' => 'required',

            'delivery_items' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0) {
                    $fail('Pre-Delivery-Items must be select min. 1 item production.');
                }
            },
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'delivery_items.*.item_id' => $msg,
            'quantity.required'         => $msg,
        ];
    }
}
