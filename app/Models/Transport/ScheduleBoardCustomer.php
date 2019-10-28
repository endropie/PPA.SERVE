<?php
namespace App\Models\Transport;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleBoardCustomer extends Pivot
{
    protected $table = "schedule_board_customers";

    protected $fillable = ['customer_id'];

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function schedule_board() {
        return $this->belongsTo('App\Models\Transport\ScheduleBoard');
    }
}
