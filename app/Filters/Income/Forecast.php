<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Forecast extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }
}
