<?php

namespace App\Traits;

use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;

trait Recurring
{

    public function createRecurring($data = [])
    {
        $request = request();
        $request->merge($data);

        if ($request->input('recurring.frequency', 'no') == 'no') {
            return;
        }

        $interval = ($request['recurring.custom']) ? (int) $request['recurring.interval'] : 1;
        $count = ($request['recurring.custom']) ? (int) $request['recurring.count'] : 0;
        $started_at = $request['started_at'] ?: now();

        $this->recurring()->create([
            'frequency' => $request['recurring.frequency'],
            'custom' => (bool) $request['recurring.custom'],
            'interval' => $interval,
            'started_at' => $started_at,
            'count' => (int) $count,
        ]);
    }

    public function updateRecurring($data = [])
    {
        $request = request();
        $request->merge($data);

        if ($request->input('recurring.frequency', 'no') == 'no') {
            if ($this->recurring) $this->recurring()->delete();
            return;
        }

        $interval = ($request['recurring.custom']) ? (int) $request['recurring.interval'] : 1;
        $count = ($request['recurring.custom']) ? (int) $request['recurring.count'] : 0;
        $started_at = $request['started_at'] ?: now();

        $recurring = $this->recurring();

        $function = $recurring->count() ? 'update' : 'create';

        $recurring->$function([
            'frequency' => $request['recurring.frequency'],
            'custom' => (bool) $request['recurring.custom'],
            'interval' => $interval,
            'started_at' => $started_at,
            'count' => (int) $count,
        ]);
    }

    public function hasRecurring($date = null) {
        return false;
    }

    public function current()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return $schedule->current()->getStart();
    }

    public function next()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        if (!$next = $schedule->next()) {
            return false;
        }

        return $next->getStart();
    }

    public function first()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return $schedule->first()->getStart();
    }

    public function last()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return $schedule->last()->getStart();
    }

    public function schedule()
    {
        $config = new ArrayTransformerConfig();
        $config->enableLastDayOfMonthFix();

        $transformer = new ArrayTransformer();
        $transformer->setConfig($config);

        return $transformer->transform($this->getRule());
    }

    public function getRule()
    {
        $rule = (new Rule())
            ->setStartDate($this->getRuleStartDate())
            ->setTimezone($this->getRuleTimeZone())
            ->setFreq($this->getRuleFrequency())
            ->setInterval($this->interval);

        // 0 means infinite
        if ($this->count != 0) {
            $rule->setCount($this->getRuleCount());
        }


        if ($this->optional) {
            $optional = explode(',', $this->optional);
            switch ($this->getRuleFrequency()) {
                case 'DAILY': $rule->setByDay($optional); break;
                case 'MONTHLY': $rule->setByMonth($optional); break;
            }
        }

        return $rule;
    }

    public function getRuleStartDate()
    {
        return new \DateTime($this->started_at, new \DateTimeZone($this->getRuleTimeZone()));
    }

    public function getRuleTimeZone()
    {
        return setting('general.timezone') ?: config('app.timezone');;
    }

    public function getRuleCount()
    {
        // Fix for humans
        return $this->count + 1;
    }

    public function getRuleFrequency()
    {
        return strtoupper($this->frequency);
    }
}
