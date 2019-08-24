<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;

class OutgoingGoodVerification extends Request
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
            $id = $this->outgoing_good_verification;
        }
        else $id = null;

        if ($method == 'POST' && Request::exists('outgoing_good_verifications')) {
            return [
                'pre_delivery_id' => 'required',
                'outgoing_good_verifications.*.item_id' => 'required',
                // 'outgoing_good_verifications.*.quantity' => 'required',
                'outgoing_good_verifications.*.unit_rate' => 'required',
                'outgoing_good_verifications.*.unit_id' => 'required',
            ];
        }

        return [
            // 'number' => ($id ? 'required|string|' : '') .'max:191|unique:outgoing_good_verifications,NULL,' . $id,
            'item_id' => 'required',
            'quantity' => 'required',
            'unit_rate' => 'required',
            'unit_id' => 'required',
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'item_id.required' => $msg,
            // 'unit_rate.required' => $msg,
        ];
    }
}
