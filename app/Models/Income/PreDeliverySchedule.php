<?php
namespace App\Models\Income;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PreDeliverySchedule extends Pivot
{
    protected $table = "pre_delivery_schedules";

    protected $fillable = ['schedule_board_id' , 'pre_delivery_id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function pre_delivery() {
        return $this->belongsTo('App\Models\Income\PreDelivery');
    }

    public function schedule_board() {
        return $this->belongsTo('App\Models\Transport\ScheduleBoard');
    }
}
