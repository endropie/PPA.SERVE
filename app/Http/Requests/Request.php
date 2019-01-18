<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{

    /**
     * Set the company id to the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        // Get request data
        $data = $this->all();

        // Reset the request data
        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
