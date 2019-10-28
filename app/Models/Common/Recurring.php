<?php

namespace App\Models\Common;

use App\Models\Model;
use App\Traits\Recurring as RecurringTrait;

class Recurring extends Model
{
    use RecurringTrait;

    protected $table = 'recurring';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['recurable_id', 'recurable_type', 'frequency', 'custom', 'interval', 'started_at', 'count'];


    /**
     * Get all of the owning recurable models.
     */
    public function recurable()
    {
        return $this->morphTo();
    }
}
