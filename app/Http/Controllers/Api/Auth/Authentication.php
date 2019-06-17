<?php
namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use Validator;

class Authentication extends Controller 
{
    public $successStatus = 200;


    public function login()
    { 
        // return response()->json(['email'=>request('email'),'password'=>request('password')], 501); 
        if(!User::where('email',request('email'))->first()) return response()->json(['message'=>'Username or email not found!'], 422);
        $attempt = Auth::attempt([
            'email'     => request('email'), 
            'password'  => request('password'),
            // 'grant_type' => "passport",
            // 'client_id ' => 1,
            // 'client_secret' => "bBnnoTXzB8sxXPCAdcrwYPYJDEQte1Vs9vAiDnDA",
        ]);

        if($attempt) {
           return $this->result(['message'=>'Login success!']);
        } 
        else { 
            return response()->json(['message'=>'Username and password not match!'], 422); 
        } 
    }

    protected function result($data) {
        $user = Auth::user();
        $user->all_permission = $user->getAllPermissions()->pluck(['name']);
        
        $newToken = $user->createToken('personal');
        $access['token'] =  $newToken->accessToken; 
        $access['expires_in']  =  $newToken->token->expires_at->diffInSeconds(Carbon()->now());

        $setting = setting()->all();

        return response()->json(
            array_merge(
                [
                    'valid'=>true, 
                    'user'=> $user, 
                    'access' => $access, 
                    'settings' => $setting
                ], 
            $data), $this->successStatus);
    }

    
    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email', 
            'password' => 'required', 
            'c_password' => 'required|same:password', 
        ]);
        
        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 422);            
        }
        
        $input = $request->all(); 
        $input['password'] =  Hash::make($input['password']); 
        $user = User::create($input); 
        $newToken = $user->createToken('personal');

        $access['token'] =  $newToken->accessToken; 
        $access['expires_in']  =  $newToken->token->expires_at->diffInSeconds(Carbon()->now());
        
        return response()->json(['access'=>$access], $this->successStatus); 
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
}