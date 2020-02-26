<?php
namespace App\Api\Controllers\Auth;

use App\Api\Controllers\ApiController;
use App\Api\Transformers\ProfileTransformer;
use App\Api\Transformers\UserTransformer;
use App\Models\Auth\User;
use Illuminate\Http\Request;

class Profile extends ApiController
{
    public function store(Request $request)
    {
        //
    }

    public function find($key)
    {
        $user = null;

        if (filter_var( $key, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $key)->first();
        }
        else if(is_string($key)) {
            $user = User::where('username', $key)->first();
        }
        else {
            $user = User::where('mobile', $key)->first();
        }

        if (!$user) {
            $user = User::findOrFail($key);
        }

        return $this->response->item($user, new UserTransformer);
    }

    public function show(Request $request)
    {
        $user = $request->user();

        $transformer = new ProfileTransformer();
        // $transformer->addExtraIncludes(['access_companies']);
        return $this->response->item($user, $transformer);
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
