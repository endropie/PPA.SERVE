<?php
namespace App\Models;

trait WithStateable
{
    // SET protected variable IN MODEL
    // protected $state_field = 'state';
    // protected $state_default = 'OPEN';

    static $STATE_FIELD = 'status';
    static $STATE_DEFAULT = 'OPEN';

    protected function getStateField() {
        return $this->state_field ?? self::$STATE_FIELD;
    }

    protected function getStateDefault() {
        return $this->state_default ?? self::$STATE_DEFAULT;
    }

    public static function bootWithStateable()
	{
        static::created(function ($model) {
            $model->setAttribute($model->getStateField(), $model->getStateDefault());
            $model->save();
            $model->moveState($model->getStateDefault());
            return $model;
        });
    }

    public function stateable()
    {
        return $this->morphMany(\App\Models\Stateable::class, 'stateable');
    }

    public function scopeState($query, $value) {
        return $query->where($this->state_field, $value);
    }

    public function scopeStateHas($query, $value) {
        $value = is_array($value) ? $value : [$value];
        return $query->whereHas('stateable', function ($q) use ($value) {
            return $q->whereIn('state', $value);
        });
    }

    public function scopeStateHasNot($query, $value) {
        $value = is_array($value) ? $value : [$value];
        return $query->whereDoesntHave('stateable', function ($q) use ($value) {
            return $q->whereIn('state', $value);
        });
    }

    public function moveState($value) {
        $this->attributes[$this->getStateField()] = $value;
        $this->save();
        $user = auth()->user();
        return $this->stateable()->create([
            'created_by' => $user->id ?? null,
            'state' => $value,
        ]);
    }
}
