<?php

namespace App\Extensions\CustomQueue;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Bus\PendingDispatch as BasePendingDispatch;

class PendingDispatch extends BasePendingDispatch
{
    public function job()
    {
        return $this->job;
    }
}
