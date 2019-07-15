<?php
namespace App\Http\GraphQL;

class Query
{
    public function items($root, array $args, $context, $info)
    {
        return \App\Models\Common\Item::all();
    }
}