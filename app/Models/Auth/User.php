<?php

namespace App\Models\Auth;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use App\Filters\Filterable;
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles, Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function contacts() {
        return \App\Models\Auth\User::where('id',"!=", $this->id)->get();
    }
   
    public function message_lastest () {
        $me = $this->id;
        return \App\Models\Tool\Message::selectRaw('*, max(created_at) as lastimeexit')
        // with('user_from','user_to')
            // ->
            ->where(function($q) use($me) {
                return $q->orWhere(['to'=>$me, 'from'=>$me]);
            })
            ->orderBy('created_at', 'desc')->get()
            ->groupBy('from')
            ;
    }
}
