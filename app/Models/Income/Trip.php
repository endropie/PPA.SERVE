<?php

namespace App\Models\Income;

use App\Models\Model;

class Trip extends Model
{
    protected $fillable = [
        'date', 'time'
    ];

    protected $appends = ['intday'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function customer ()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function getIntdayAttribute()
    {
        return \Carbon\Carbon::parse($this->date)->dayOfWeekIso;
    }

    public function scopeDailyScheduler($query, $date, $reset = false)
    {
        if ($reset) static::where('date', $date)->delete();

        foreach (Customer::all() as $customer) {
            $customer_trips = $customer->customer_trips()
                ->where('intday', $date->dayOfWeekIso)
                ->get()
                ->each(function($item) use($customer, $date) {
                    $customer->trips()
                        ->updateOrCreate([
                            'customer_id' => $customer->id,
                            'date' => $date->format('Y-m-d'),
                            'time' => $item->time,
                        ]);
                });

        }
    }
}
