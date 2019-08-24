<?php

namespace App\Models\Tool;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'users';    
    
    public function scopeBetween($query, $user1, $user2)
    {
        return $query->orWhere(function($query) use($user1, $user2){
            return $query->where(['to' => $user1, 'from'=> $user2]);
        })->orWhere(function($query) use($user1, $user2){
            return $query->where(['to' => $user2, 'from'=> $user1]);
        });
    }
}
