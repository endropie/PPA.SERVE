<?php
namespace App\Http\Controllers\Api;

use App\Filters\Filter;
use App\Http\Controllers\ApiController;
use App\Models\Commentable;
use Illuminate\Http\Request;

class Commentables extends ApiController
{
    public function index (Filter $filter)
    {
        $limit  = (integer) request('limit', 20);
        $offset = (integer) request('offset', 0);
        $latest = request('latest', null);
        $loadup = request('loadup', null);

        $query = Commentable::filter($filter)->when($latest, function($q) use($latest, $loadup) {
            if (!$latest) return $q;

            $dt = \Carbon\Carbon::create($latest);
            return $q->where('created_at', ($loadup ? '>' : '<='), $dt);
        });

        $total = $query->count();
        $lastime  = $latest ?? $query->max('created_at') ?? date('Y-m-d H:i:s');

        $data  = $loadup
            ? $query->limit($limit)->oldest()->orderBy('id')->get()
            : $query->limit($limit)->offset($offset)->latest()->orderByDesc('id')->get();

        if ($loadup && $data->count()) {
            $total = $total + $data->count();
            $lastime = $data->max('created_at')->format('Y-m-d H:i:s');
        }

        return response()->json([
            "data" => $data,
            "total" => $total,
            "latest" => $lastime,
        ]);
    }
}
