<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class IncomingGood extends Request
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
            $id = $this->incoming_good;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        // For "RETURN" Transations, ORDER MODE must be "NONE"
        if ($this->input('transaction') == 'RETURN') $this->merge(['order_mode' => 'NONE']);

        $in_customer = Rule::in(\App\Models\Income\Customer::where('id', request('customer_id'))->get()->pluck('id'));
        $in_customer_item = Rule::in(
            \App\Models\Common\Item::where('customer_id', request('customer_id'))
            ->when(request('transaction') == 'SAMPLE', function ($q) { return $q->sampled(); })
            ->get()->pluck('id')
        );

        return [
            'number' => ($id ? 'required|' : '') .'unique:incoming_goods,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'indexed_number' => ($id ? 'required|' : '') .'unique:incoming_goods,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'customer_id' => ['required', $in_customer],
            'date' => 'required',
            'time' => 'required',
            'transaction' => 'required|in:REGULER,RETURN,SAMPLE',

            'incoming_good_items.*.quantity' => 'required',
            'incoming_good_items.*.unit_id' => 'required',
            'incoming_good_items.*.unit_rate' => 'required',
            'incoming_good_items.*.item_id' => ['required', $in_customer_item],
            'incoming_good_items' =>
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
            'incoming_good_items.*.item_id' => $msg,
            'quantity.required'         => $msg,
        ];
    }
}
