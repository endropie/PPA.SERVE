<?php
namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use App\Http\Controllers\ApiController;
use App\Models\Auth\User;
use Lcobucci\JWT\Parser;
use Validator;

class Authentication extends ApiController
{
    public $successStatus = 200;


    public function login()
    {
        if(!User::where('email',request('email'))->first()) return response()->json(['message'=>'Username or email not found!'], 422);
        $attempt = Auth::attempt([
            'email'     => request('email'),
            'password'  => request('password'),
        ]);

        if($attempt) {
            Passport::tokensExpireIn(now()->addDays(1));
            Passport::refreshTokensExpireIn(now()->addDays(7));
            return $this->result(['message'=>'Login success!']);
        }
        else {
            return response()->json(['message'=>'Username and password not match!'], 422);
        }
    }

    protected function result($data) {
        $user = Auth::user();
        $user->all_permission = $user->getAllPermissions()->pluck(['name']);

        $valid = $user->createToken('PPA Personal Access Client');

        $setting = setting()->all();

        return response()->json(
            array_merge(
                [
                    'success'=>true,
                    'user'=> $user,
                    'token' => $valid->accessToken,
                    'expires_in' => $valid->token->expires_at->diffInSeconds(now()),
                    'settings' => $setting
                ],
            $data), $this->successStatus);
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,NULL,NULL',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $input = $request->all();
        $input['password'] =  Hash::make($input['password']);
        $user = User::create($input);

        return $this->login($request);
    }

    public function user(Request $request)
    {
        $user = Auth::user();
        return response()->json(['auth' => $user, 'head' => $request->header], $this->successStatus);
    }

    public function validToken(Request $request)
    {
        return  $this->result(['message'=>'The token valid to use.']);
    }

    public function setChangePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'password' => ['required', function($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Current Password is invalid.');
                }
            }],
            'newpassword' => 'required:min:8',
            'c_newpassword' => 'required|same:newpassword',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $user->update([
          'password' =>  Hash::make($request->newpassword)
        ]);

        return response()->json(['success' => true, 'user' => $user], $this->successStatus);
    }

    public function logout () {
        // $request = request();
        // $value = $request->bearerToken();
        // $id = (new Parser())->parse($value)->getHeader('jti');
        // $token = $request->user()->tokens->find($id);

        $token = request()->user()->token();
        $token->revoke();

        return response()->json([
            'message' => 'You have been succesfully logged out!'
        ]);
    }
}
