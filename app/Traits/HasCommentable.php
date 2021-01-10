<?php

namespace App\Traits;

trait HasCommentable
{
    public function commentables()
    {
        return $this->morphMany(\App\Models\Commentable::class, 'commentable');
    }

    public function setComment($values)
    {
        if (gettype($values) == 'string') $values = ['text' => $values];

        return $this->commentables()->create(array_merge($values, ['is_log' => false]));
    }

    public function setCommentLog($values)
    {
        if (gettype($values) == 'string') $values = ['text' => $values];

        return $this->commentables()->create(array_merge($values, ['is_log' => true]));
    }
}
