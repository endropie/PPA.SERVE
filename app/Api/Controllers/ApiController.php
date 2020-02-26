<?php
namespace App\Api\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    use Helpers;

    protected $DATABASE = \DB::class;

    protected function throwValidationException(Request $request, $validator) {
        throw new ValidationHttpException($validator->getMessageBag()->toArray());
    }
    protected function validateByValidator(Request $request, $validator) {
        if ($validator->fails()){
            $this->throwValidationException($request, $validator);
        }
    }

    protected function error($message, $code = 501) {

        if (gettype($message) != 'string') $message = json_encode($message);
        return $this->response->error($message, $code = 501);
    }
}
