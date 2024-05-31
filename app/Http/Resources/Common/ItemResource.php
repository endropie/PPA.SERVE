<?php
namespace App\Http\Resources\Common;

use App\Http\Resources\Resource;

class ItemResource extends Resource
{

    public function toArray($request)
    {
        return [
            $this->mergeAttributes(),
        ];
    }
}
