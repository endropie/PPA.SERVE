<?php

namespace App\Models\Tool;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['text', 'to', 'from'];

    public function user_to () {
        return $this->belongsTo('App\Models\Auth\User', 'to');
    }

    public function user_from () {
        return $this->belongsTo('App\Models\Auth\User', 'from');
    }

    public function scopeUserID($query, $userID)
    {
        return $query->where(function($query) use($userID){
            return $query->orWhere('to', $userID)->orWhere('from', $userID);
        });
        // return $query->where('to', $userID);
    }

    public function scopeBetween($query, $sender, $receiver)
    {
        return $query->orWhere(function($query) use($sender, $receiver){
            return $query->where(['to' => $sender, 'from'=> $receiver]);
        })->orWhere(function($query) use($sender, $receiver){
            return $query->where(['to' => $receiver, 'from'=> $sender]);
        });
    }
}
