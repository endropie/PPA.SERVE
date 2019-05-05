<?php
use Carbon\Carbon;

if (!function_exists('carbon')) {
    function carbon($time = null, $tz = null) {
        return new Carbon($time, $tz);
    }
}