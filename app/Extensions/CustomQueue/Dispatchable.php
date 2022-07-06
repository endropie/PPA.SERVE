<?php

namespace App\Extensions\CustomQueue;

use App\Extensions\CustomQueue\PendingDispatch;
use Illuminate\Foundation\Bus\Dispatchable as BaseDispatchable;

trait Dispatchable
{
    use BaseDispatchable;

    public static function dispatch()
    {
        return new PendingDispatch(new static(...func_get_args()));
    }

}
