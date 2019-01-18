<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;
use Route;

use App\Traits\Eloquence;

class Model extends Eloquent
{
    use Eloquence;

    public function __construct()
     {   
        // ====== Multi-databases =======
        
        // $new = config()->get('database.connections.mysql');
        // $new['database'] = 'gds_virmata';
        // config()->set('database.connections.new', $new);

        // $this->connection = 'new';
    }

    public function getIsRelatedAttribute()
    {
        return false;
    }
}
