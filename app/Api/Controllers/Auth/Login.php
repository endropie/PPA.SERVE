<?php
namespace App\Api\Controllers\Auth;

use App\Api\Controllers\ApiController;
use App\Api\Transformers\NullObjectTransformer;
use App\Api\Transformers\ProfileTransformer;
use App\Models\Auth\User;
use App\Models\NullObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends ApiController
{

    public function store(Request $request)
    {
        $this->validateLogin($request);
        $this->attemptLogin($request);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string|email',
            'password' => 'required|string',
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        if (Auth::guard("web")->once($this->credentials($request)) ){
            $this->sendLoginResponse($request);
        } else {
            $this->sendFailedLoginResponse($request);
        }
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    protected function sendLoginResponse(Request $request)
    {
        $inputs = $request->all();
        $user = User::where($this->username(), $inputs[$this->username()])->firstOrFail();
        $passport = $user->createToken('Personal Access Client');

        $meta = array(
            'status_code' => 200,
            'status_text' => "OK",
            'message' => trans("auth.login.success"),
            'token' => $passport->accessToken
        );
        $response = $this->response->item($user, new ProfileTransformer)->setMeta($meta);

        $response->throwResponse();
    }

    protected function authenticated(Request $request, $user)
    {

    }

    protected function sendFailedLoginResponse(Request $request)
    {
        if ($user = User::where($this->username(), $request->input($this->username()))->first()) {
            $this->response()->errorUnauthorized(trans('auth.login.nismatch'));
        }
        else {
            $this->response()->errorUnauthorized(trans('auth.login.failed'));
        }
    }

    public function username()
    {
        return 'email';
    }
    /**
     * Log the user out of the application.
     *
     * The logout procedure just deletes the personal access token
     * which was created by Passport. You can also just revoke them
     * or incorporate refresh tokens. Do as you like.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        $request->user()->token()->delete();
        $meta = array(
            'status_code' => 200,
            'status_text' => "OK",
            'message' => trans("auth.logout.success"),
        );
        $response = $this->response->item(new NullObject(), new NullObjectTransformer())
            ->setStatusCode(200)
            ->setMeta($meta);
        // Use this method instead of send(). It also saves you from weird
        // assertJsonStructure errors
        $response->throwResponse();
    }
}
