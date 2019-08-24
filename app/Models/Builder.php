<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder as Eloquent;

class Builder extends Eloquent
{
    public function messageBetween($builder, $value)
    {
        return $builder->between($value['sender'], $value['receiver']);
    }

    public function messageUser($builder, $value)
    {
        return $builder->userID($value);
    }
    
    public function messagePartner($builder, $value)
    {
        return $builder->userID($value);
    }
}