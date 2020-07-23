<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class DeportationGood extends Request
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
            $id = $this->deportation_good;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        $in_customer = Rule::in(\App\Models\Income\Customer::where('id', request('customer_id'))->get()->pluck('id'));

        return [
            'number' => ($id ? 'required|' : '') .'unique:deportation_goods,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'customer_id' => ['required', $in_customer],
            'date' => 'required',

            'deportation_good_items.*.quantity' => 'required',
            'deportation_good_items.*.unit_id' => 'required',
            'deportation_good_items.*.unit_rate' => 'required',
            'deportation_good_items.*.item_id' => 'required',
            'deportation_good_items.*.stockist_from' => 'required',
            'deportation_good_items' =>
                function ($attribute, $value, $fail) {
                    if (sizeof($value) == 0) {
                        $fail('Part Detail must be select min. 1 part item.');
                    }
                }
        ];
    }

    public function messages()
    {
        $msg = 'The field is failed!';

        return [
            'deportation_good_items.*.item_id' => $msg,
            'quantity.required'         => $msg,
        ];
    }
}
