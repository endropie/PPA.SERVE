<?php

namespace App\Models;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

class Commentable extends Model
{
    use Filterable;

    protected $fillable = ['text', 'is_log'];

    protected $appends = ['user'];

    protected static function booted()
    {
        static::creating(function ($comment) {
            if (auth()->user()) {
                $comment->created_by = auth()->user()->id;
            }
            elseif (env('APP_ENV')) {
                $user = \App\Models\User::first();
                $comment->created_by = $user->id;
            }

            return $comment;
        });
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function replies()
    {
        return $this->morphMany(self::class, 'commentable');
    }

    public function created_user()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function getUserAttribute()
    {
        $user = $this->created_user()->first();
        return $user->only(['name', 'email', 'avatar']);
    }
}
