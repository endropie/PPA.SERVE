<?php

namespace App\Traits;

trait HasCommentable
{
    public function commentables()
    {
        return $this->morphMany(\App\Models\Commentable::class, 'commentable');
    }

    public function setComment(string $values, string $type = 'GENERAL')
    {
        if (gettype($values) == 'string') $values = ['text' => $values];

        return $this->commentables()->create(array_merge($values, ['is_log' => false, 'type' => $type]));
    }

    public function setCommentLog(string $values, string $type = 'GENERAL')
    {
        if (gettype($values) == 'string') $values = ['text' => $values];

        return $this->commentables()->create(array_merge($values, ['is_log' => true, 'type' => $type]));
    }
}
