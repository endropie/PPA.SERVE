<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $DATABASE = \DB::class;

    public function __construct()
    {
        # Code..
    }

    protected function assignMiddleware($value, $crud = false)
    {
        // No need to check for permission in console
        if (app()->runningInConsole()) return;

        if ($crud) {
            $this->middleware('permission:' . $value . '-create')->only(['create', 'store', 'duplicate', 'import']);
            $this->middleware('permission:' . $value . '-read')->only(['index', 'show', 'edit', 'export']);
            $this->middleware('permission:' . $value . '-update')->only(['update', 'enable', 'disable']);
            $this->middleware('permission:' . $value . '-delete')->only('destroy');
        } else {
            $this->middleware('permission:' . $value);
        }
    }

    public function relationships($model, $relationships)
    {
        $counter = array();
        foreach ($relationships as $relationship => $text) {
            $relation = explode('.', $relationship);
            $model = $model;
            for ($i = 0; $i < count($relation); $i++) {
                $function = $relation[$i];
                $model = $model->$function();

                if ($i == count($relation) - 1 && $c = $model->count()) {
                    // dd($c, $function, $model);
                    $counter[] = $c . ' ' . strtolower($text);
                }
            }
        }
        return $counter;
    }

    public function error($body = 'Rosource is not Allowed!', $code = 501)
    {
        if (is_string($body)) $body = ['message' => $body];
        abort(response()->json($body, $code));
    }
}
