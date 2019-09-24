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

    protected function preparing () {
        if (request('transaction') == 'RETURN') $this->merge(['order_mode' => 'NONE']);
    }

    public function rules()
    {
        $this->preparing();
        // Check if store or update
        $method = $this->getMethod();

        if ($method == 'PATCH' || $method == 'PUT') {
            $id = $this->incoming_good;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|' : '') .'unique:incoming_goods,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'date' => 'required',
            'time' => 'required',
            'customer_id' => 'required',

            'transaction' => 'required|in:REGULER,RETURN',
            // 'order_mode' => 'required|in:NONE'.(request('transaction') == 'RETURN' ? '' : ',PO,ACCUMULATE'),

            'incoming_good_items.*.item_id' => [
                'required',
                Rule::in(\App\Models\Common\Item::where('customer_id', request('customer_id'))->get()->pluck('id')),
            ],
            'incoming_good_items' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0) {
                    $fail('Part Item must be select min. 1 part item.');
                }
            },
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
