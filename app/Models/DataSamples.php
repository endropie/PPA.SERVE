<?php
namespace App\Models;

use App\Scopes\SampleScope;

trait DataSamples
{

    public function scopeWithSampled ($query) {
        return $query->withoutGlobalScope(SampleScope::class);
    }

    public function scopeSampled ($query) {
        return $query->withoutGlobalScope(SampleScope::class)->where('sample', 1);
    }

    public static function bootDataSamples()
	{
		static::addGlobalScope(new SampleScope);
	}

}
