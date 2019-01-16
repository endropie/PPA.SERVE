<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    
    public function __construct()
    {
        # Code..
    }

    protected function assignMiddleware($value){
        // No need to check for permission in console
        if (app()->runningInConsole()) return false;
        else{    
        
            $this->middleware('permission:'. $value .'-create')->only(['create', 'store', 'duplicate', 'import']);
            $this->middleware('permission:'. $value .'-read')->only(['index', 'show', 'edit', 'export']);
            $this->middleware('permission:'. $value .'-update')->only(['update', 'enable', 'disable']);
            $this->middleware('permission:'. $value .'-delete')->only('destroy');
        }
    }


}
